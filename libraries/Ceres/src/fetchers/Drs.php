<?php

namespace Ceres\Fetcher;

use Ceres\Util\DataUtilities;
use Ceres\Util\StringUtilities;

require_once(CERES_ROOT_DIR . '/src/fetchers/Rest.php');

class Drs extends Rest {

    protected string $endpoint = "https://repository.library.northeastern.edu/api/v1";
    private string $pageParamName = 'page';

    public function __construct(array $queryOptions = array(), array $queryParams = array(), $resourceId = null) {
        parent::__construct($queryOptions, $queryParams, $resourceId);

        if (is_null($resourceId)) {
        $this->resourceId = $this->getPidFromSettings();
        } else {
        $this->resourceId = $resourceId;
        }
    }

    public function buildQueryString($queryOptions = false, $queryParams = false): void {
        if (! $queryOptions) {
        $queryOptions = $this->queryOptions;
        }
        
        if (! $queryParams) {
        $queryParams = $this->queryParams;
        }
        
        $url = $this->endpoint;
        $url .= '/' . $queryOptions['action'];

        if (isset($queryOptions['sub_action'])) {

        //DRS subaction of content_objects has special needs for building the URL
        //PMJ assuming this only gets invoked when the action is 'files'
        switch ($this->queryOptions['sub_action']) {
            case 'content_objects':
            $url .= "{$this->resourceId}/{$queryOptions['sub_action']}";
            break;
            
            case null:
            //do nothing since there's no subaction
            $url .= "{$this->resourceId}";
            break;
            
            default:
            //most common url construction
            $url .= "/{$queryOptions['sub_action']}/{$this->resourceId}";
            break;
        }
        } else {
        $url .= '/' . $this->resourceId;
        }
        
        if (! empty($queryParams)) {
        $url .= '?';
        foreach ($queryParams as $param=>$value) {
            if ($param == 'q') {
            $value = StringUtilities::sanitizeTextField($value);
            }
            $url .= "$param=$value&";
        }
        }
        
        $dau = constant("DRS_API_USER");
        $dap = constant("DRS_API_PASSWORD");
        
        if(!(empty($dau) || empty($dap))) {

            $token = $this->drsAuth();
            if ($token != false && is_string($token))
                $url .= "token=$token";
            }
        
            //@TODO: fix how the query string gets set and used
            //return $url;
    }

    public function fetchPage(int $pageNumber) {
        $this->setQueryParam($this->pageParamName, $pageNumber);
        $this->fetchData();
    }
    
    public function getPageUrl(int $pageNumber) {
        // $this->setQueryParam($this->pageParamName, $pageNumber);
        $queryOptions = [
            'action' => 'search',
            'sub_action' => 'av',
        ];
        
        $paramsOptions = [
            'page' => $pageNumber,
            
        ];
        
        $url = $this->buildQueryString($queryOptions, $paramsOptions);
        return $url;
    }
    
    public function setPaginationData() {
        $paginationData = $this->responseData['output']['pagination']['table'];
        $this->currentPage = $paginationData['current_page'];
        $this->pageCount = $paginationData['num_pages'];
        $this->perPage = $paginationData['per_page'];
        error_log($this->currentPage);
        error_log($this->pageCount);
        error_log($this->perPage);
    }
    
    public function fetchFileData($resourceId) {
        $queryString = $this->endpoint . "/files/$resourceId";
        $fileData = $this->fetchData($queryString, true);
        return $fileData['output'];
    }

    public function fetchAssociatedFileData($resourceId) {
        $fileData = $this->fetchFileData($resourceId);
        if (isset($fileData['associated'])) {
        $associatedFileData = $fileData['associated'];
        //PHP 7 has array_first_key() to avoid this reset/key stuff, but I can't assume PHP7
        reset($associatedFileData);
        $associatedPid = key($associatedFileData);
        return $this->fetchFileData($associatedPid);
        }
        return false;
    }
    /**
     * The DRS part of the nee DRS Toolkit sets basically a default collection or set to draw from in the plugin's
     * settings, so this maintains using that setting if a $resourceId is omitted
     * 
     * @return string
     */

    public function getPidFromSettings() {
        //$collectionSetting = $this->getFetcherOptionValue('drstk_collection');
        //$collectionSetting = get_option('drstk_collection');
        $explodedCollectionSetting = explode("/", $collectionSetting);
        return end($explodedCollectionSetting);
    }

    public function parseItemsData(): void {
        $this->itemsData = $this->responseData['output']['response']['response']['docs'];
    }

    /*DRS API Authenticate helper method*/
    // @TODO consider if this can be rolled into the rest of the fetching system?
    // comes from a site-specific customization in DRSTK
    public function drsAuth() {
        
        if(drstk_api_auth_enabled() == true){
        // Token is only good for one hour
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://repository.library.northeastern.edu/api/v1/auth_user");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "email=" . DRS_API_USER . "&password=" . DRS_API_PASSWORD);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $headers = array();
        $headers[] = "Content-Type: application/x-www-form-urlencoded";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
        
        // result should be json
        $data = json_decode($result, true);
        
        $token = $data["auth_token"];
        
        if (!empty($token)) {
            return $token;
        } else {
            return false;
        }
        }
        else {
        // No user and/or password set
        return false;
        }
    }

    public function parseJwPlayerData($itemId) {
        $itemData = $this->getItemDataById($itemId);
        $mediaPid = $this->parseContentObjectId($itemId);
        
        $imageUrl = 'https://repository.library.northeastern.edu' . $itemData['fields_thumbnail_list_tesim'][3];
        $plainMediaUrl = "https://repository.library.northeastern.edu/wowza/$mediaPid/plain";
        $playlistMediaUrl = "https://repository.library.northeastern.edu/wowza/$mediaPid/playlist.m3u8";
        
        switch ($itemData['canonical_class_tesim'][0]) {
        case 'AudioFile':
            $type = 'mp3';
            break;
            
        case 'VideoFile':
            $type = 'mp4';
            break;
        }
        return array($plainMediaUrl, $playlistMediaUrl, $type, $imageUrl);
    }

    public function parseContentObjectId($itemId) {
        //it's somewhat amiss to not use the methods in this class to build the string
        //but if I'm looping through a search result I don't want to reset the params
        //and break pagination
        
        $queryString = $this->endpoint . "/files/$itemId/content_objects";
        $contentObjectsData = $this->fetchData($queryString, true);

        reset($contentObjectsData['output']['canonical_object']);
        $canonicalObjectUrl = key($contentObjectsData['output']['canonical_object']);
        $canonicalObjectUrlParts = explode('/', $canonicalObjectUrl);
        
        $parsedCanonicalObjectUrl = parse_url($canonicalObjectUrl);
        $explodedPath = explode('/', $parsedCanonicalObjectUrl['path']);
        
        //explodedPath looks like /downloads/{pid}
        $canonicalObjectId = $explodedPath[2];
        return $canonicalObjectId;
    }
    
    public function fetchFilesData($resourceId) {
        $queryString = $this->endpoint . "/files/$resourceId";
        $filesData = $this->fetchData($queryString, true);
        return $filesData;
    }
}
