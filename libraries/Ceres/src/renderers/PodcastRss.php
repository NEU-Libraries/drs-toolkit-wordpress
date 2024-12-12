<?php 

namespace Ceres\Renderer;

class PodcastRss extends AbstractRenderer
{
  protected $fetcher; //@TODO align this with abstract
  public function render(): string {
    
    $this->fetcher->fetchData();
 
    $rss = "<?xml version='1.0' encoding='UTF-8'?><rss version='2.0'
      	xmlns:content='http://purl.org/rss/1.0/modules/content/'
      	xmlns:wfw='http://wellformedweb.org/CommentAPI/'
      	xmlns:dc='http://purl.org/dc/elements/1.1/'
      	xmlns:atom='http://www.w3.org/2005/Atom'
      	xmlns:sy='http://purl.org/rss/1.0/modules/syndication/'
      	xmlns:slash='http://purl.org/rss/1.0/modules/slash/'
      	xmlns:itunes='http://www.itunes.com/dtds/podcast-1.0.dtd'
      	>
    ";
    do_action( 'rss_tag_pre', 'rss2' );
    $rss .= "<channel>
    	<title>" . get_bloginfo('name') . "</title>
    	<atom:link href='" . get_bloginfo('url') . "?feed=podcast' rel='self' type='application/rss+xml' />
    	<link>" . get_bloginfo('url') . "</link>
    	<itunes:explicit>no</itunes:explicit>
    	<itunes:image href='" . $this->getOption('rssImageUrl') . "' />
      <itunes:category text='Education'></itunes:category>
    	<itunes:author>" . get_option('drstk_podcast_author') . "</itunes:author>
    	<itunes:owner>
        <itunes:name>Northeastern University</itunes:name>
        <itunes:email>library-podcastteam@northeastern.edu</itunes:email>
    	</itunes:owner>
    	<description>" . get_bloginfo("description") . "</description>
    	<lastBuildDate></lastBuildDate>
    	<language>en-US</language>
    	<sy:updatePeriod>hourly</sy:updatePeriod>
    	<sy:updateFrequency>1</sy:updateFrequency>
      ";

    do {
      $this->fetcher->parseItemsData();
      $itemsData = $this->fetcher->getItemsData();
      foreach($itemsData as $itemData) {
        $rss .= $this->renderPodcastArticle($itemData);
      }
      // I (PMJ) not entirely happy with this pagination technique,
      // but we'll see if something better reveals itself
      if ($hasNextPage = $this->fetcher->hasNextPage()) {
        $this->fetcher->fetchNextPage();
      }
    } while ($hasNextPage);
    
    $rss .= "</channel></rss>";
    return $rss;
    
  }
  

  public function build(): void {
    
  }
  
  public function renderPodcastArticle($itemData) {
    // @TODO: This ties the Renderer to the DRS_Fetcher explicitly to the DRS. I want to avoid that
    // that's a more general problem of not having a normalized metadata structure
    $podcastArticleRss =
    "<item>
  		<title>" . $itemData['title_info_title_ssi'] ."</title>
  		<link>https://repository.library.northeastern.edu/files/" . $itemData['id'] . "</link>
  		<pubDate>" . $itemData['date_ssi'] . "</pubDate>
  		<dc:creator><![CDATA[]]></dc:creator>
  		<category><![CDATA[Education]]></category>
  
  		<guid isPermaLink='false'>" . $itemData['identifier_tesim'][0] . "</guid>
  		<description><![CDATA[" . $itemData['abstract_tesim'][0] . "]]></description>
      <enclosure url='https://repository.library.northeastern.edu/files/" . $itemData['id'] . "/audio.mp3' type='audio/mpeg' />
		</item>";
    
    return $podcastArticleRss;
  }
  
}


