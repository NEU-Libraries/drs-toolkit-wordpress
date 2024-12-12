<?php 

namespace Ceres\Renderer;

class Pagination extends Html {
  
  public function render(): string {
    // @TODO needs update to reflect possibility of multiple fetchers being injected
    $pageCount = $this->fetchers['drs_paginator']->getPageCount();
    
    // this likely has to parse it out from url, maybe something in the response header
    // which means it'll be API-dependent
    // unless it's hanging out in the fetcher, depending on how well I'm paying attention to state
    //$currentPageNumber = $this->getCurrentPageNumber();
    $currentPageNumber = 1;
    
    $firstPageUrl = $this->fetchers['drs_paginator']->getPageUrl(1);
    $lastPageUrl = $this->fetchers['drs_paginator']->getPageUrl($pageCount);
    $firstButton = "<a class='pagination-button' data-url='$firstPageUrl'>FIRST</a>";
    $lastButton = "<a class='pagination-button' data-url='$lastPageUrl'>LAST</a>";
    $html = "<div class='ceres-pagination'>$firstButton";
    for($pageNumber = 1; $pageNumber < $pageCount + 1; $pageNumber++) {
      //$url = $this->getFetcher->getPageUrl($pageNumber);
      $classes = 'pagination-button ';
      
      if ($pageNumber == $currentPageNumber) {
        $classes .= 'current';
      }
      //$pageButton = "<a class='$classes' data-url='$url'>$pageNumber</a>";
      
      //$html .= $pageButton;
    }
    
    $html .= $lastButton .= "</div>";
    return $html;
  }


  public function build(): void {
    
  }
}
