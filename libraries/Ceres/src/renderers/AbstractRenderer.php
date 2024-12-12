<?php

namespace Ceres\Renderer;

use Ceres\Exception\CeresException;
use Ceres\Util\StringUtilities as StrUtil;
use Ceres\Exception\Data as DataException;
use Ceres\Exception\Data\UnexpectedData as UnexpectedDataException;

abstract class AbstractRenderer {

    protected ?array $rendererOptions = [];

    /**
     * The Ceres_Abstract_Fetcher(s) that are handling the data retrieval. Its itemData property
     * holds the data to work with in most simple cases
     * 
     * Child classes' render() method will tell the Fetchers to, yaknow, fetch data
     * as needed for it to put together the page (or page component).
     * 
     * @var array Ceres_Abstract_Fetcher
     */

    protected array $fetchers = [];

    /**
     * The Extractor(s) that will be used to wrangle raw responses from the Fetchers
     * 
     * extract() method on extractors will return just the needed data
     * 
     * @var array Ceres_Abstract_Extractor
     * 
     */

    protected array $extractors = [];

    protected array $requiredProperties = [];

    protected array $optionalProperties = [];

    //the data coming from an Extractor to render
    protected array $renderArray = [];

    //used only during bounceback from Fetcher directly to Renderer, skipping Extractor
    protected ?string $responseData = null;

    protected ?string $jsonToInject;

    public function __construct(array $fetchers = [], array $extractors = [], $rendererOptions = []) {
      
        foreach ($fetchers as $classObj) {
            if (! is_a($classObj, 'Fetcher')) {
                throw new CeresException("not a fetcher");
            }
            $this->injectFetcher($classObj);
        }

        foreach ($extractors as $classObj) {
            if (! is_a($classObj, 'Extractor')) {
            throw new CeresException("not an extractor");
            }
            $this->injectExtractor($classObj);
        }

        if (! empty($rendererOptions)) {
            $this->setRendererOptions($rendererOptions);
        }
    }
    /**
     * setRenderArrayFromFile
     *
     * Expects a text file with a serialized php array or json string
     * 
     * @param string $fileName
     * @return void
     */
    public function setRenderArrayFromFile(string $fileName) {
        $this->renderArray = unserialize(file_get_contents($fileName));
    }


    public function setRenderArrayFromArray(array $renderArray) {
        $this->renderArray = $renderArray;
    }


    public function setJsonToInjectFromFile(string $fileName, bool $decodeJson = false) {
        $jsonToRender = file_get_contents($fileName);
        if ($decodeJson) {
            $this->jsonToInject = json_decode($jsonToRender, true);
        } else {
            $this->jsonToInject = $jsonToRender;
        }
    }

    public function setJsonToInject(?string $extractorName): void {
        if (is_null($extractorName)) {
            $allExtractors = array_values($this->extractors);
            $extractor = $allExtractors[0];
        } else {
            $extractor = $this->extractors[$extractorName];
        }
        $this->jsonToInject = $extractor->getJsonToInject();
    }

    //@todo for the bounceback option
    public function setRenderArrayFromFetcher(?string $fetcherName = null): void {
        if (is_null($fetcherName)) {
            $allFetchers = array_values($this->fetchers);
            $fetcher = $allFetchers[0];
        } else {
            $fetcher = $this->fetchers[$fetcherName];
        }
        $this->renderArray = $fetcher->getResponseData();
    }

    //@todo for the bounceback option
    public function setJsonToInjectFromFetcher(?string $fetcherName = null): void {
        $fetcher =  $this->getFetcher($fetcherName);
        $fetcher->fetchData();
        $this->jsonToInject = $fetcher->getResponseData();
    }

    //@todo this is newish, and needs to be used elsewhere w/in fcns
    public function getFetcher(?string $fetcherName = null): object {
        if (is_null($fetcherName)) {
            $allFetchers = array_values($this->fetchers);
            $fetcher = $allFetchers[0];
        } else {
            $fetcher = $this->fetchers[$fetcherName];
        }
        return $fetcher;
    }

    //@todo this is newish, and needs to be used elsewhere w/in fcns
    public function getExtractor(?string $extractorName = null): object {
        if (is_null($extractorName)) {
            $allExtractors = array_values($this->extractors);
            $extractor = $allExtractors[0];
        } else {
            $extractor = $this->fetchers[$extractorName];
        }
        return $extractor;
    }

    public function setRenderArray(?string $extractorName = null): void {
        if ($this->getRendererOptionValue('bounceBack')) {
            $fetcher = $this->getFetcher();
            $responseData = $fetcher->fetchData();
            //echo $responseData;
            $this->responseData = $responseData;
            $responseData = $this->responseData;
            //$this->setRenderArrayFromFetcher();

            return;
        }
        if ($this->getRendererOptionValue('bounceBackJsonToInject')) {
            $this->setJsonToInjectFromFetcher();
            return;
        }
        if (is_null($extractorName)) {
            $allExtractors = array_values($this->extractors);
            $extractor = $allExtractors[0];
        } else {
            $extractor = $this->extractors[$extractorName];
        }
        $this->renderArray = $extractor->getRenderArray();
    }



    /* enqueing will have to figure out how to stuff styles and scripts in early in WP rendering.
    Might have to go elsewhere */
    // @todo move these to WP-specific in an adapter?
    protected function enqueStyles() {

    }

    protected function enqueScripts() {
        
    }

    // @TODO this might get moved into a separate Pagination Renderer, likely different for each Fetcher
    //   First thought is that this'd just instantiate a new Renderer and tell it to do its thing
    //   though that'd also mean injecting the relevant Fetcher into _that_ which might be 
    //   getting crazy

    // abstract function buildPagination();



    public function setRendererOptions(array $options) {
        $this->rendererOptions = $options;
    }

    /**
     * Returns the entire options array
     * 
     * @return array
     */

    public function getRendererOptions() {
        return $this->rendererOptions;
    }

    /**
     * Set or unset an option value. Not passing a value unsets the option.
     * 
     * @param string $option
     * @param string $value
     */
    public function setRendererOptionValue($option, $value = '') {
        if ($value == '') {
            unset($this->rendererOptions[$option]);
        } else {
            $this->rendererOptions[$option] = $value;
        }
    }

    public function getRendererOptionValue($option) {
        if (isset($this->rendererOptions[$option])) {
            return $this->rendererOptions[$option];
        }
      // throw something
    }

    public function setFetcherOptionsValues(string $fetcherName, array $optionValues): void {
    }

    public function setExtractorOptionsValues(string $extractorName, array $optionValues): void {
    }

    public function setFetcherOptionValue(?string $fetcherName = null, $optionName, $optionValue) {
        $fetcher = $this->getFetcher($fetcherName);
        $fetcher->setFetcherOptionValue($optionName, $optionValue);
    }

    public function setExtractorOptionValue(?string $extractorName, $optionName, $optionValue) {
        $extractor = $this->getExtractor($extractorName);
        $extractor->setExtractorOptionValue($optionName, $optionValue);

    }

    public function renderMissingData($expectedPropName) {
    }

    public function injectFetcher($fetcher, $description = null) {
      
        //for if/when I have multiple fetchers
        $name = StrUtil::createNameIdForInstantiation($fetcher, $description);
        $name = StrUtil::uniquifyName($name, $this->fetchers );
        //$this->fetchers[$name] = $fetcher;


        $this->fetchers[] = $fetcher;
        return $name;
    }

    public function injectExtractor(object $extractor, $description = null): string {
        
        //for if/when I have multiple extractors
        $name = StrUtil::createNameIdForInstantiation($extractor, $description);
        $name = StrUtil::uniquifyName($name, $this->extractors );
        //$this->extractors[$name] = $extractor;

        $this->extractors[] = $extractor;
        return $name;
    }

    protected function spawnSubRenderer(string $subRendererName) {
        $subRenderer = new $subRendererName;
        return $subRenderer;
    }
}
