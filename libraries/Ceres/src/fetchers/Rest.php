<?php

namespace Ceres\Fetcher;


class Rest extends AbstractFetcher {


    /**
     * refers to additional URL path options, generally for a RESTful API pattern
     * @var array
     */

    protected ?array $queryOptions;

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




    public function __construct(array $queryOptions = array(), array $queryParams = array(), $resourceId = null ) {
        $this->setQueryParams($queryParams);
        $this->setQueryOptions($queryOptions);
        $this->setResourceId($resourceId);
    }
    
    // abstract override
    public function buildQueryString(?array $queryOptions = null, ?array $queryParams = null): void {
    }

    public function setPaginationData() {
    }

    public function getItemDataById($itemId) {
    }

    public function parseItemsData(): void
    {
        
    }

    public function detectResponseFormat() {}

    public function fetchPage(int $pageNumber) {
    }

    public function getPageUrl(int $pageNumber) {
    }
    // end abstract override

    public function getQueryParams(): array {
        return $this->queryParams;
    }

    public function setQueryParam($param, $value = null ): void {
        if (is_null(($value))) {
            unset($this->queryParams[$param]);
        } else {
        $this->queryParams[$param] = $value;
        }
    }

    public function getQueryParam($param): string {
        return $this->queryParams[$param];
    }

    public function setQueryOptions(?array $queryOptions = null): void {
        $this->queryOptions = $queryOptions;
    }

    public function getQueryOptions(): array {
        return $this->queryOptions;
    }

    public function setQueryOptionValue($option, $value = null) {
        if (is_null($value)) {
            unset($this->queryOptions[$option]);
        } else {
            $this->queryOptions[$option] = $value;
        }
    }

    public function getQueryOption($option) {
        return $this->queryOptions[$option];
    }

    public function setResourceId(?string $resourceId = null): void {
        $this->resourceId = $resourceId;
    }

    public function getResourceId(): string {
        return $this->resourceId;
    }

}


