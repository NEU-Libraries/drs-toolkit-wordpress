<?php 

namespace Ceres\Fetcher;

class GraphQl {


        // abstract override
        public function buildQueryString($queryOptions = false, $queryParams = false) {
        }
    
        public function setPaginationData() {
        }
    
        public function getItemDataById($itemId) {
        }
    
        public function parseItemsData()
        {
            
        }
    
        public function detectResponseFormat() {}
    
        public function fetchPage(int $pageNumber) {
        }
    
        public function getPageUrl(int $pageNumber) {
        }
        // end abstract override
    
}
