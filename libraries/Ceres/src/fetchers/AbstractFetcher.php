<?php 

    namespace Ceres\Fetcher;

use Ceres\Util\DataUtilities;

abstract class AbstractFetcher {

    protected string $endpoint;

    protected string $scope = 'ceres';

    protected string $query;

    protected string $method = 'GET'; //usually GET, sometime POST. Others unimplemented

    /**
     * refers to additional URL path options, generally for a RESTful API pattern
     * @var array
     */

    protected ?array $queryOptions;

    protected array $fetcherOptions;
    
    /**
     * The ID of the remote resource (DRS pid, DPLA hash id, etc)
     * @var string
     */

    protected ?string $resourceId;

    /**
     * GET params to tack on to the $endpoint + $queryOptions path
     * @var array
     */

    protected ?array $queryParams;

    /**
     * The parsed response, including the handling of errors and output message (i.e., not the direct
     * curl response, though that's up for @TODO debate
     * @var array
     */

    protected $responseData;

    /**
     * The items data, parsed out from the response
     * @TODO: figure out if/how to normalize this across APIs to decouple Fetchers from Renderers
     * 
     * @var array
     */

    protected array $itemsData;

    /**
     * The number of pages from a large API request. Will depend on the requested items per page,
     * so that's better not changing between requests.
     * 
     * @var integer
     */

    protected int $pageCount;

    /** 
     * If the API provides the option, the set number of items to return per page. Best not to change this 
     * between requests. Should be set from setQueryParams() or setQueryParam().
     * 
     * @var integer
     */

    protected $perPage;

    /**
     * For rolling through multiple requests to the API to gather data, the current page number.
     * Should be set by the fetch*Page() functions.
     * 
     * @var integer
     */

    protected $currentPage;

    abstract protected function buildQueryString(?array $queryOptions = null, ?array $queryParams = null): void;

    abstract protected function parseItemsData(): void;

    abstract protected function fetchPage(int $pageNumber);

    abstract protected function getPageUrl(int $pageNumber);

    /**
     * Takes API-specific response to set currentPage, pageCount, and perPage
     * Usually this appears in the response data somewhere, but sometimes
     * needs to use get_headers() when the data is there
     * 
     * @param Array $responseData
     */
    abstract public function setPaginationData();

    abstract public function getItemDataById(string $itemId);

    public function __construct(?array $queryOptions = null, ?array $queryParams = null, ?string $resourceId = null, ?array $fetcherOptions = null ) {
        $this->setQueryParams($queryParams);
        $this->setQueryOptions($queryOptions);
        $this->setResourceId($resourceId);
        $this->setFetcherOptions($fetcherOptions);

        /* set properties from the fetcherOptions */
        //@todo pull this up from WDQS  $this->setQueryFromFile();
        

    }

    /**
     * fetchDataFromFile
     *
     * @param string $jsonFilePath
     * @return void
     */

     //@todo needs an easy trigger for when to do this instead of an API req
    public function fetchDataFromJsonFile(string $jsonFilePath): string {
        return file_get_contents($jsonFilePath);
    }

    /**
     * The params are to to bypass the usual class-based props, e.g. when needing to 
     * query just a snippet that diverges from the 'starting point' of the fetcher,
     * like DRS grabbing content_object data when looping through a search response
     * 
     * @param $url
     * @param boolean $returnWithoutSetting Just send back the data, but don't keep it in the prop
     */
    public function fetchData(?string $url = null, bool $returnWithoutSetting = false) {

        if (DataUtilities::valueForOption('fetchLocalData', $this->scope)) {
            $jsonFilePath = DataUtilities::valueForOption('localResponseDataPath', $this->scope);
            $responseData = $this->fetchDataFromJsonFile($jsonFilePath);
            if($returnWithoutSetting) {
                return $responseData;
            }
            
            $this->responseData = $responseData;
            return null;
            //@todo remove the repetition from the bottom of this function
        }
        $ch = curl_init();
        
        switch ($this->method) {
            case 'GET':
                if (is_null($url)) {
                    $url = $this->buildQueryString(); // build entire URL, including params as part of it
                }
                curl_setopt($ch, CURLOPT_HTTPGET, true);
            break;
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                $postFields = $this->buildQueryString();
            break;
            default:

            break;

        }


        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_USERAGENT, "CERES/develop p.murray-john@northeastern.edu");
        $rawResponse = curl_exec($ch);
        $responseStatus = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        if (! $responseStatus) {
            $responseStatusArray = curl_getinfo($ch);
            $responseStatus = $responseStatusArray['http_code'];
        }
        
        
        // shenanigans from https://stackoverflow.com/questions/10384778/curl-request-with-headers-separate-body-a-from-a-header
        // for splitting out just the body from the response
        $header_len = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $responseBody = substr($rawResponse, $header_len);
        // end shenanigans 
        
        $output = $responseBody;
        switch ($responseStatus) {
            case 200:
                $output = $responseBody;
                $statusMessage = 'OK';
                break;
            case 403:
                $output = "Forbidden -- is access correct?";
                $statusMessage = 'Forbidden';
                break;
            case 404:
                $output = 'The resource was not found.';
                $statusMessage = 'Not Found';
                break;
            case 302:
                $output = $responseBody;
                $statusMessage = 'The resource has moved or is no longer available';
                break;

            case 400:
                $output = $responseBody;
                $statusMessage = 'Bad Request (no biscuit!)';

                break;
            default:
                $output = 'An unknown error occured.' . $responseStatus;
                $statusMessage = 'An unkown error occured. Please try again';
                break;
        }
        $responseData = array(
            'status' => $responseStatus,
            'statusMessage' => $statusMessage,
            // leave it to the instantiated classes to parse the output
            // usually it'll just be json_decode($output, true), but might be XML
            // or something even more funky
            'output' => $output,
        );
        
        if($returnWithoutSetting) {
            return $responseData;
        }
        
        $this->responseData = $responseData;
        curl_close($ch);
        $this->setPaginationData();
    }

    public function hasNextPage(): bool {
        $nextPage = $this->currentPage + 1;
        if ($nextPage > $this->pageCount) {
        return false;
        }
        return true;
    }
    
    public function fetchNextPage() {
        if ($this->hasNextPage()) {
            $nextPage = $this->currentPage + 1;
            $this->fetchPage($nextPage);
        }
    }
    
    public function fetchFirstPage() {
        $this->fetchPage(1);
    }
    
    public function fetchLastPage() {
        $lastPage = $this->pageCount - 1;
        $this->fetchPage($lastPage);
    }

    public function getResponseData() {
        return $this->responseData;
    }

    // @TODO: setters should probably do some minimal sanity checking so they all have key - value of the right types
    
    public function setQueryParams(?array $queryParams = null) {
        $this->queryParams = $queryParams;
    }

    public function getQueryParams(): array {
        return $this->queryParams;
    }

    public function setQueryParam(string $param, $value = '' ): void {
        if ($value == '') {
            unset($this->queryParams[$param]);
        } else {
            $this->queryParams[$param] = $value;
        }
    }

    public function getQueryParam($param): string {
        return $this->queryParams[$param];
    }

    public function setScope(string $scope): void {
        $this->scope = $scope;
    }

    public function setQueryOptions(?array $queryOptions = null): void {
        $this->queryOptions = $queryOptions;
    }

    public function getQueryOptions(): array {
        return $this->queryOptions;
    }

    public function setQueryOption(string $option, string $value = ''): void {
        if ($value == '') {
            unset($this->queryOptions[$option]);
        } else {
            $this->queryOptions[$option] = $value;
        }
    }

    public function setFetcherOptions(?array $fetcherOptions): void {
        if (! is_null($fetcherOptions)) {

            foreach ($fetcherOptions as $optionName) {
            // echo $optionName;
                $this->fetcherOptions[$optionName] = DataUtilities::valueForOption($optionName);
            }
        }
    }

    //@todo another one to abstract across F/E/Rs, probably as a Trait
    public function setFetcherOptionValue(string $optionName, $optionValue, bool $asCurrentValue = false) {
        if ($asCurrentValue) {
            $this->fetcherOptions[$optionName]['currentValue'] = $optionValue;    
        } else {
            $this->fetcherOptions[$optionName] = $optionValue;
        }
    }


    public function getFetcherOptions(): array {
        return $this->fetcherOptions;
    }

    protected function getFetcherOptionValue(string $option): string {
        return 'string';
    }

    public function setResourceId(?string $resourceId = null): void {
        $this->resourceId = $resourceId;
    }

    public function getResourceId(): string {
        return $this->resourceId;
    }

    public function getItemsData() {
        return $this->itemsData;
    }
    
    public function getPageCount(): int {
        return $this->pageCount;
    }

    public function setEndpoint(string $endpointURL): void {
        $this->endpoint = $endpointURL;
    }
    public function setQuery(string $query): void {
        $this->query = $query;
    }

    public function setQueryFromFile(string $file): void {
        $this->query = file_get_contents($file);
    }

    public function setResponseDataFromFile(string $responseJsonFile): void {
        $this->responseData = file_get_contents($responseJsonFile);
    }
}
