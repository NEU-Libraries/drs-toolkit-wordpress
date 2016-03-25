<?php
/* adds shortcode */
add_shortcode( 'drstk_timeline', 'drstk_timeline' );
function drstk_timeline( $atts ){
  global $errors;
  $cache = get_transient(md5('PREFIX'.serialize($atts)));

  if($cache) {
      return $cache;
  }
  $neu_ids = explode(", ",$atts['id']);
  
  $event_list = array();
  $timeline_html = "";
  foreach($neu_ids as $neu_id){
    $url = "https://repository.library.northeastern.edu/api/v1/files/" . $neu_id;
    $data = get_response($url);
    $data = json_decode($data);
    
    if (!isset($data->error)){
      $pid = $data->pid;
      $key_date = $data->key_date;
      $current_array = array();
      $breadcrumbs = $data->breadcrumbs;      
      
      $thumbnail_url = $data->thumbnails[2];
      
      $caption_headline_tag = "neu:5m60qx652";
      $caption = $breadcrumbs->$caption_headline_tag;
      
      $headline = $breadcrumbs->$caption_headline_tag;
      $text = $breadcrumbs->$pid;
     
      $keys = (array)$key_date;      
      $key_date_explode = explode("/",array_keys($keys)[0]);
      
      
      $timeline_html .= "<div class='timelineclass' data-url='".$thumbnail_url."' data-caption='".$caption."' data-credit=' ' data-year='".$key_date_explode[0]."' data-month='".$key_date_explode[1]."' data-day='".$key_date_explode[2]."' data-headline='".$headline."' data-text='".$text."'>";
      $timeline_html .= "</div>";
    }else {
      $timeline_html = $errors['shortcodes']['fail'];
    }
  }
  $shortcode = "<div id='timeline-embed' style=\"width: 100%; height: 600px\"></div>";
  $shortcode .= "<div id='timeline'>".$timeline_html."</div>";
  $cache_output = $shortcode;
  $cache_time = 1000;
  set_transient(md5('PREFIX'.serialize($atts)) , $cache_output, $cache_time * 60);
  return $shortcode;
}

function drstk_timeline_shortcode_scripts() {
	global $post;
	if( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'drstk_timeline') ) {
    wp_register_script( 'drstk_timelinejs',
        plugins_url( '../assets/js/timeline.js', __FILE__ ),
        array( 'jquery' ));
    wp_enqueue_script('drstk_timelinejs');
    wp_register_style( 'drstk_timelinejs_css',plugins_url('../assets/css/timeline.css', __FILE__));
    wp_enqueue_style( 'drstk_timelinejs_css');
    wp_register_script( 'drstk_timeline',
        plugins_url( '../assets/js/timeline/timelinepage.js', __FILE__ ),
        array( 'jquery' ));
    wp_enqueue_script('drstk_timeline');
	}
}
add_action( 'wp_enqueue_scripts', 'drstk_timeline_shortcode_scripts');
