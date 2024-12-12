<?php
/* adds shortcode */
add_shortcode( 'drstk_tiles', 'drstk_tiles' );
add_shortcode( 'drstk_tile', 'drstk_tiles' );
function drstk_tiles( $atts ){
  $errors = drstk_get_errors();
  $cache = get_transient(md5('DRSTK'.serialize($atts)));

  if($cache != NULL
      && ! WP_DEBUG
      && (!(isset($params))
          || $params == NULL)
      && !(isset($atts['collection_id']))
      ) {
          return $cache;
  }
      
  $imgs = array_map('trim', explode(',', $atts['id']));
  $img_html = "";
  if (isset($atts['image-size'])){
    $num = $atts['image-size']-1;
  } else {
    $num = 4;
  }
  foreach($imgs as $img){
    $repo = drstk_get_repo_from_pid($img);
    if ($repo != "drs"){$pid = explode(":",$img); $pid = $pid[1];} else {$pid = $img;}
    if ($repo == "drs"){
      $url = drstk_api_url("drs", $img, "files", NULL, "solr_only=true");
      $response = get_response($url);
      $data = json_decode($response['output']);
      $data = $data->_source;
      $thumbnail = "https://repository.library.northeastern.edu".$data->fields_thumbnail_list_tesim[$num];
    }
    if ($repo == "wp"){
      $post = get_post($pid);
      $data = new StdClass;
      $meta = wp_get_attachment_metadata($pid); //get sizes
      if (isset($meta['sizes'])){
        $thumb_base = wp_get_attachment_thumb_url($pid);
        $thumb_base = explode("/",$thumb_base);
        $arr = array_pop($thumb_base);
        $thumb_base = implode("/", $thumb_base);
        if ($num == 1){ $thumbnail = $thumb_base."/".$meta['sizes']['thumbnail']['file'];}
        if ($num == 2){ $thumbnail = $thumb_base."/".$meta['sizes']['medium']['file'];}
        if ($num == 3){ $thumbnail = $thumb_base."/".$meta['sizes']['medium']['file'];}
        if ($num == 4){
         if (isset($meta['sizes']['large'])){
           $thumbnail = $thumb_base."/".$meta['sizes']['large']['file'];
         } else {
           $thumbnail = drstk_home_url()."/wp-content/uploads/".$meta['file'];
         }
        }
        if ($num == 5){
         if (isset($meta['sizes']['large'])){
           $thumbnail = $thumb_base."/".$meta['sizes']['large']['file'];
         } else {
           $thumbnail = drstk_home_url()."/wp-content/uploads/".$meta['file'];
         }
        }
      } else {
        $thumbnail = drstk_home_url()."/wp-includes/images/media/video.png";
      }
      $master = $post->guid;
      $data->full_title_ssi = $post->post_title;
      $data->abstract_tesim = array($post->post_excerpt);
      $pid = "wp:".$pid;
    }
    if ($repo == "dpla"){
      $url = drstk_api_url("dpla", $pid, "items");
      $response = get_response($url);
      $dpla = json_decode($response['output']);
      if (isset($dpla->docs[0]->object)){
        $url = $dpla->docs[0]->object;
      } else {
        $url = DPLA_FALLBACK_IMAGE_URL;
      }
      $title = $dpla->docs[0]->sourceResource->title;
      if (isset($dpla->docs[0]->sourceResource->description)){
        $description = $dpla->docs[0]->sourceResource->description;
      } else {
        $description = "";
      }
      $master = $url;
      $thumbnail = $url;
      $data = new StdClass;
      $data->full_title_ssi = array($title);
      $data->abstract_tesim = array($description);
      $data->creator_tesim = is_array($dpla->docs[0]->sourceResource->creator) ? $dpla->docs[0]->sourceResource->creator : array($dpla->docs[0]->sourceResource->creator);

      $data->date_ssi = array($dpla->docs[0]->sourceResource->date->displayDate);
      $pid = "dpla:".$pid;
    }
    $type = isset($atts['type']) ? $atts['type'] : $atts['tile-type'];
    if (!isset($data->error)){
      // $pid = $data->id;
      if (isset($atts['metadata'])){
        $img_metadata = '';
        $metadata = explode(",",$atts['metadata']);
        foreach($metadata as $field){
           if (isset($data->$field)){
             $this_field = $data->$field;
            if (isset($this_field)){
              if (is_array($this_field)){
                foreach($this_field as $val){
                  if (is_array($val)){
                    $img_metadata .= implode("<br/>",$val) . "<br/>";
                  } else {
                    $img_metadata .= $val ."<br/>";
                  }
                }
              } else {
                $img_metadata .= $this_field . "<br/>";
              }
            }
          }
        }
      }
      if ($type == 'pinterest-below' || $type == 'pinterest'){
        $img_html .= "<div class='brick'><a href='".drstk_home_url()."item/".$pid."'><img src='".$thumbnail."'></a><div class='info wp-caption-text'><a href='".drstk_home_url()."item/".$pid."'>".$img_metadata."</a>";
      }
      if ($type == 'pinterest-hover'){
        $img_html .= "<div class='brick brick-hover'><img src='".$thumbnail."' style='width:100%'><div class='info wp-caption-text'><a href='".drstk_home_url()."item/".$pid."'>".$img_metadata."</a>";
      }
      if ($type == 'even-row' || $type == 'square'){
        $img_html .= "<div class='cell' data-thumbnail='".$thumbnail."'><div class='info wp-caption-text'><a href='".drstk_home_url()."item/".$pid."'>".$img_metadata."</a>";
      }
      $img_html .= "<div class=\"hidden\">";
      foreach($data as $key=>$field){
        if ($key != "all_text_timv" && $key != "object_profile_ssm"){
          if (is_array($field)){
            foreach($field as $key=>$field_val){
              $img_html .= $field_val . "<br/>";
            }
          } else {
            $img_html .= $field . "<br/>";
          }
        }
      }
      $img_html .= "</div>";
      $img_html .= "</div></div>";
    } else {
      $img_html = $errors['shortcodes']['fail'];
    }

  }
  $shortcode = "<div class='freewall' id='freewall' data-type='".$type."'";
  if (isset($atts['cell-height'])){ $shortcode .= " data-cell-height='".$atts['cell-height']."'";} else {$shortcode .= " data-cell-height='200'";}
  if (isset($atts['cell-width'])){ $shortcode .= " data-cell-width='".$atts['cell-width']."'";} else {$shortcode .= " data-cell-width='200'";}
  if (isset($atts['text-align'])){ $shortcode .= " data-text-align='".$atts['text-align']."'";} else {$shortcode .= " data-text-align='center'";}
  $shortcode .= ">".$img_html."</div>";
  $cache_output = $shortcode;
  $cache_time = 1000;
  set_transient(md5('DRSTK'.serialize($atts)) , $cache_output, $cache_time * 60);
  return $shortcode;
}

function drstk_tile_shortcode_scripts() {
	global $post, $wp_query;
	if( is_a( $post, 'WP_Post' ) && (has_shortcode( $post->post_content, 'drstk_tiles') || has_shortcode( $post->post_content, 'drstk_tile')) && !isset($wp_query->query_vars['drstk_template_type']) ) {
    wp_register_script('drstk_freewall',
        DRS_PLUGIN_URL . "/assets/js/freewall/freewall.js",
        array( 'jquery' ));
    wp_enqueue_script('drstk_freewall');
    wp_register_script( 'drstk_tiles',
        DRS_PLUGIN_URL . '/assets/js/tiles.js',
        array( 'jquery' ));
    wp_enqueue_script('drstk_tiles');
	}
}
add_action( 'wp_enqueue_scripts', 'drstk_tile_shortcode_scripts');
