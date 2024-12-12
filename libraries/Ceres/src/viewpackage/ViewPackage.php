<?php

namespace Ceres\ViewPackage;

use Ceres\Renderer;
use Ceres\Exception\CeresException;
use Ceres\Util\StringUtilities as StrUtil;
use Ceres\Util\DataUtilities as DataUtil;

class ViewPackage {

    protected string $nameId;
    protected string $humanName;
    protected string $description;
    protected string $parentViewPackage;
    protected string $projectName;
    protected $renderer;
    protected array $currentViewPackageData = [];
    private  array $allViewPackagesData = [];

    public function __construct(string $nameId) {
        
        $this->allViewPackagesData = DataUtil::getWpOption('ceres_view_packages');
        $this->load($nameId);
    }

    public function load(string $nameId) {
        $this->nameId = $nameId;
        $this->currentViewPackageData = $this->allViewPackagesData[$nameId];
        $rendererData = $this->currentViewPackageData['renderer'];
        $rendererOptions = [];
        //skip the second level key to get at array of info about renderer
        $rendererClassInfo = DataUtil::skipArrayLevel($rendererData);
        $rendererOptions = $rendererClassInfo['options'];
      
        foreach ($rendererOptions as $optionName) {
            $rendererOptions[$optionName] = DataUtil::valueForOption($optionName, $this->nameId);
        }
    }

    public function build() {
        $this->buildRenderer();

        //@todo shit. buildRenderer already has buildExtractor and
        //buildFetcher inside it, so I'm redundant here
        //
        // what's the way, though, to build an e/f and have the renderer
        // inject it into itself?
        $this->buildExtractor();
        $this->buildFetcher();
    }

    public function buildRenderer() {
        $rendererData = $this->currentViewPackageData['renderer'];
        $rendererClassInfo = DataUtil::skipArrayLevel($rendererData);
        $rendererName = $rendererClassInfo['fullClassName'];
        $this->renderer = new $rendererName;
        foreach ($rendererClassInfo['options'] as $index => $optionName) {
            $optionValue = DataUtil::valueForOption($optionName, $this->nameId);
            $rendererClassInfo['options'][$optionName] = $optionValue;
            unset($rendererClassInfo['options'][$index]);
        }
        $this->renderer->setRendererOptions($rendererClassInfo['options']);

        $extractor = $this->buildExtractor();
        
        if ($extractor) {
            $this->renderer->injectExtractor($extractor, 'test extractor');
        }
        
        $fetcher = $this->buildFetcher();
        if ($fetcher) {
            $this->renderer->injectFetcher($fetcher, 'test fetcher');
        }

    }

    public function buildExtractor(string $shortName = null) {
        $data = $this->currentViewPackageData['extractors'];
        if (empty($data)) {
            return null;
        }

        if ($shortName) {
            $classInfo = $data[$shortName];
        } else {
            $classInfo = DataUtil::skipArrayLevel($data);
        }
        
        $className = $classInfo['fullClassName'];

        foreach ($classInfo['options'] as $index => $optionName) {
            $optionValue = DataUtil::valueForOption($optionName, $this->nameId);
            $classInfo['options'][$optionName] = $optionValue;
            unset($classInfo['options'][$index]);
        }

        $extractor = new $className;
        return $extractor;
    }

    public function buildFetcher(string $shortName = null) {
        $data = $this->currentViewPackageData['fetchers'];

        if (empty($data)) {
            return null;
        }
        if ($shortName) {
            $classInfo = $data[$shortName];
        } else {
            $classInfo = DataUtil::skipArrayLevel($data);
        }
        $className = $classInfo['fullClassName'];
        $fetcher = new $className;
        $fetcher->setScope($this->nameId);
        //print_r($classInfo);
        $fetcher->setFetcherOptions($classInfo['options']);
        return $fetcher;
    }

    public function setNameId($humanName) {
        $snakeCasedName = StrUtil::languageToSnakeCase($humanName);
        $existingViewPackageNames = array_keys(self::$allViewPackagesData);
        $nameId = StrUtil::uniquifyName($snakeCasedName, $existingViewPackageNames);
        $this->nameId = $nameId;
    }

    public function getNameId() {
        return $this->nameId;
    }

    public function getRenderer() {
        return $this->renderer;
    }

    public function setProjectName() {
        //$siteUrl = get_option('siteurl');
        $siteUrl = DataUtil::getWpOption(('siteurl'));
        $siteName = preg_replace("(^https?://)", "", $siteUrl);
        $this->projectName = $siteName;
    }

    /**
     * 
     * filters specific options (e.g., tabular) out
     * of general options
     *
     * @param array $options
     * @return void
     */


     //did this become unneccesary??? or redundant?
    public function filterGeneralOptionsByScope($options) {
        foreach( $options as $scope => $suboptions) {
            // $scope is 'general','tabular' etc for grouping inputs
            if ($scope != 'general') {
                $scopeKeys = array_keys($options[$scope]);
                foreach ($scopeKeys as $key) {
                    if(array_key_exists($key, $options['general'])) {
                        unset($options['general'][$key]);
                    }
                }
            }
        }
        return $options;
    }

    /* wrappers around Renderer methods */ 
    
    public function render(): string {
        return $this->renderer->render();
    }

    public function setFetcherQueryFromFile(?string $fetcherName = null, string $file) {
        $fetcher = $this->renderer->getFetcher($fetcherName);
        $fetcher->setQueryFromFile($file);
    }

    public function setFetcherOptionValue(?string $fetcherName = null, string $optionName, string $optionValue) {
        $this->renderer->setFetcherOptionValue($fetcherName, $optionName, $optionValue);
    }

    public function setExtractorOptionValue(?string $extractorName = null, string $optionName, string $optionValue ) {
        $this->renderer->setExtractorOptionValue($extractorName, $optionName, $optionValue);
    }

    public function gatherData(?string $extractorName = null, ?string $pathToMockFetcherResponse = null,?string $pathToMockExtractorData = null) {   
        // params for renderer->setRenderArray are:
        // $renderer(<extractorName>, <pathToMockFetcherResponse>, <pathToMockExtractorData>)    )
        // the variations are usually used for debugging or super-complicated combos
        // the first sans params usually works
        //@todo is the above true?

        //@todo the logic here for the permutations needs some TLC, along with how Renderers deal with what they get
        if (is_null($extractorName) 
            && is_null($pathToMockExtractorData)
            && is_null($pathToMockFetcherResponse)) {
            $this->renderer->setRenderArray();
        } else if (!is_null($pathToMockFetcherResponse)) {
            $this->renderer->setRenderArray(null, $pathToMockFetcherResponse);
        } else if (!is_null($pathToMockExtractorData)) {
            $this->renderer->setRenderArray(null, null, $pathToMockExtractorData);
        }

    }


    public function renderFullHtml(): string {
        return $this->renderer->renderFullHtml();
    }
    /* end Renderer wrappers */

/**
 * 
 * For data created by content creator to override the defaults,
 * and the $currentValue
 * 
 * Need currentValues loaded first in order to override them
 */

    function overrideOptionValue($value, $optionArray) {

    }

    function userOptionAccess($user, $option) {
        $userCeresRole = $user->getCeresRole(); //totally fake, need to figure this out
        if(in_array($userCeresRole, $option['access'])) {
            return true;
        }
        return false;
    }

    public function export($includeValues) {
        // toArray() -> addValues
    }

    public function clone(bool $includeValues = false, bool $save = true) {
        // toArray() -> new VP 
        // append something to human name
        // prepend something to description

        $vpArray = [];
        $newVpNameId = StrUtil::uniquifyName($this->nameId, $this->allViewPackagesData);
        $newViewPackage = new ViewPackage($newVpNameId);
        if ($save) {
            $newViewPackage->save();
        } else {
            return $vpArray;
        }
        // save()
    }

    public function save() {
        $vpArray = $this->toArray();
        $vpArray = [];
        $allViewPackages = DataUtil::getWpOption('ceres_view_packages');
        $allViewPackages[] = $vpArray;
        //update_option('ceres_view_packages', $allViewPackages);
        DataUtil::updateWpOption('ceres_view_packages', $allViewPackages);
    }

    /**
     * toArray
     *
     * recreate an export/importable array of all the settings
     * @return array
     */
    public function toArray() {
        //mimic/ recreate the dev template
        // what does PHP native to array do?
        // or just serialize in its current state?

    }
}

