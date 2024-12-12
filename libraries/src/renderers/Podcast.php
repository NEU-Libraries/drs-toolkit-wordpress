<?php

namespace Ceres\Renderer;
/* temp ignore this 
class Podcast extends Html {
  protected $fetcher;
  protected $jwPlayerOptions = array();
  
  public function render(): string {
    //$this->fetcher->fetchData();
    $html = "";
    do {
      //$this->fetcher->parseItemsData();
      $itemsData = $this->fetcher->getItemsData();
      foreach($itemsData as $itemData) {
        $html .= $this->renderPodcastArticle($itemData);
      }
      // I (PMJ) not entirely happy with this pagination technique,
      // but we'll see if something better reveals itself
      //$hasNextPage = $this->fetcher->hasNextPage();
      $hasNextPage = false;
      if ($hasNextPage) {
        //$this->fetcher->fetchNextPage();
      }
    } while ($hasNextPage);

    //@todo update to where pagination lands
    //$html .= $this->buildPagination();
    return $html;
  }

  public function renderPodcastArticle($itemData) {
    $itemFilesData = $this->fetcher->fetchFilesData($itemData['id']);

    // @TODO: This ties the Renderer to the DRS_Fetcher explicitly to the DRS. I want to avoid that.
    // that's a more general problem of not having a normalized metadata structure
    // the class='row' business also ties it to the theme or SiteBuilder plugin crap
    
    $associatedFileData = $this->fetcher->fetchAssociatedFileData($itemData['id']);
    
    if ($associatedFileData) {
      //PHP 7 has array_first_key() to avoid this reset/key stuff, but I can't assume PHP7
      reset($associatedFileData['canonical_object']);
      $transcriptionLink = key($associatedFileData['canonical_object']);
      $transcriptionDownloadHtml = "<li><a href='$transcriptionLink'><strong>Download Transcription</strong></a></li>";
    } else {
      $transcriptionDownloadHtml = '';
    }

    $podcastArticleHtml = 
    "<div class='row'>
         <article class='ceres-podcast'>
            <h3>" . $itemData['title_info_title_ssi'] . "</h3>
  									<p>" . implode('; ', $itemData['personal_creators_tesim']) . "</p>
  									<p>" . $itemData['date_ssi'] . "</p>
  									<p>" . $itemData['abstract_tesim'][0] . "</p>
                    <div>" . $this->renderJwplayer($itemData['id'], $this->getJwPlayerOptions()) . "</div>
                    <ul class='drs-podcast-downloads'>
  										<li>
                        <a href='https://repository.library.northeastern.edu/files/" . $itemData['id'] . "/audio.mp3'>
  											 <strong>Download Episode</strong>
  										  </a>
                      </li>
                      $transcriptionDownloadHtml
                    </ul>
					</article>
    </div>";

    return $podcastArticleHtml;
  }


  public function build(): void {
    
  }

  public function renderJwplayer($resourceId, $options = array()) {
    $jwplayerRenderer = new Jwplayer($this->fetcher, $resourceId, $options);
    return $jwplayerRenderer->render();    
  }
  
  public function setJwPlayerOptions($jwPlayerOptions) {
    $this->jwPlayerOptions = $jwPlayerOptions;
  }
  
  public function getJwPlayerOptions() {
    return $this->jwPlayerOptions;
  }
}
*/

