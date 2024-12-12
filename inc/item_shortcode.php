<?php
/* adds shortcode */
add_shortcode( 'drstk_item', 'drstk_item' );
add_shortcode( 'drstk_single', 'drstk_item' );
function drstk_item( $atts ){
  $cache = get_transient(md5('DRSTK'.serialize($atts)));

  if($cache != NULL
      && ! WP_DEBUG
      && (!(isset($params))
          || $params == NULL)
      && !(isset($atts['collection_id']))
      ) {
          return $cache;
  }

  $repo = drstk_get_repo_from_pid($atts['id']);
  if ($repo != "drs"){$pid = explode(":",$atts['id']); $pid = $pid[1];} else {$pid = $atts['id'];}
  if (isset($atts['image-size'])){
    $num = $atts['image-size']-1;
  } else {
    $num = 3;
  }
  if ($repo == "drs"){
    $url = drstk_api_url("drs", $pid, "files", NULL, "solr_only=true");
    $response = get_response($url);
    $data = json_decode($response['output']);
    $data = $data->_source;
    $thumbnail = "https://repository.library.northeastern.edu".$data->fields_thumbnail_list_tesim[$num];
    $master = "https://repository.library.northeastern.edu".$data->fields_thumbnail_list_tesim[4];

    $objects_url = drstk_api_url("drs", $pid, "files", "content_objects");
    $response = get_response($objects_url);
    $objects_data = json_decode($response['output']);
    $data = (object) array_merge((array) $data, (array) $objects_data);
    foreach($data->content_objects as $key=>$val){
      if ($val == 'Large Image'){
        $master = $key;
      }
    }
    $data->mods = new StdClass;
    $data->mods->Title = $data->title_info_title_tesim;
    $abs = "Abstract/Description";
    $data->mods->$abs = $data->abstract_tesim;
    if (isset($data->creator_tesim)){
      $data->mods->Creator = $data->creator_tesim;
    }
    $dat = "Date Created";
    if (isset($data->key_date_ssi)){
      $data->mods->$dat = array($data->key_date_ssi);
    }
    if (isset($data->date_ssi)){
      $data->mods->$dat = array($data->date_ssi);
    }
  }
  if ($repo == "wp"){
    $post = get_post($pid);
    $data = new StdClass;
    $data->canonical_object = new StdClass;
    $url = $post->guid;
    if (strpos($post->post_mime_type, "audio") !== false){
      $type = "Audio File";
    } else if (strpos($post->post_mime_type, "video") !== false){
      $type = "Video File";
    } else {
      $type = "Master Image";
    }
    $data->canonical_object->$url = $type;
    $meta = wp_get_attachment_metadata($pid); //get sizes
    $thumb_base = wp_get_attachment_thumb_url($pid);
    if (isset($meta['sizes'])){
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
      $thumbnail = null;
    }
    $master = $post->guid;
    $data->mods = new StdClass;
    $data->mods->title = array($post->post_title);
    $data->mods->caption = array($post->post_excerpt);
    $data->id = $post->ID;
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
    $data = new StdClass;
    $data->canonical_object = new StdClass;
    $type = "Master Image";
    $data->canonical_object->$url = $type;
    $thumbnail = $url;
    $master = null;
    $data->mods = new StdClass;
    $title = array($dpla->docs[0]->sourceResource->title);
    $data->mods->Title = $title;
    if (isset($dpla->docs[0]->sourceResource->description)){
      $description = $dpla->docs[0]->sourceResource->description;
    } else {
      $description = "";
    }
    $abs = "Abstract/Description";
    $data->mods->$abs = $description;
    if (isset($dpla->docs[0]->sourceResource->creator)){
      $data->mods->Creator = is_array($dpla->docs[0]->sourceResource->creator) ? $dpla->docs[0]->sourceResource->creator : array($dpla->docs[0]->sourceResource->creator);
    }
    $dat = "Date Created";
    $data->mods->$dat = array($dpla->docs[0]->sourceResource->date->displayDate);
    $data->id = $pid;
  }



  $html = "<div class='drs-item'>";

  $jwplayer = false; // note: unneeded if there is only one canonical_object type

  if (isset($atts['display-video']) && isset($data->canonical_object)){
    foreach($data->canonical_object as $key=>$val){
      if (($val == 'Video File' || $val == 'Audio File') && $atts['display-video'] == "true" ){
        if ($repo == "wp"){
          $html .= do_shortcode('[video src="'.$master.'"]');
        } else {
          $html .= insert_jwplayer($key, $val, $data, $thumbnail);
        }
        $jwplayer = true;
      }
    }
  }

  if (!$jwplayer) {
    if (isset($atts['display-issuu']) && isset($data->drs_location_url_ssim)){
      $location_href = $data->drs_location_url_ssim[0];
      $issu_id = explode('?',$location_href);
      $issu_id = explode('=',$issu_id[1]);
      $issu_id = $issu_id[1];
      $html .= '<div data-configid="'.$issu_id.'" style="width:100%; height:500px;" class="issuuembed"></div><script type="text/javascript" src="//e.issuu.com/embed.js" async="true"></script>';
      $html .= "<a href='".drstk_home_url()."item/".$atts['id']."'>View Item Details</a>";
    } else {
      $html .= "<a href='".drstk_home_url()."item/".$atts['id']."'><img class='drs-item-img' id='".$atts['id']."-img' src='".$thumbnail."'";

      if (isset($atts['align'])){
        $html .= " data-align='".$atts['align']."'";
      }
      if (isset($atts['float'])){
        $html .= " data-float='".$atts['float']."'";
      }

      if (isset($atts['zoom']) && $atts['zoom'] == 'on' && $master != null && check_master($master) == true){
        $html .= " data-zoom-image='".$master."' data-zoom='on'";
        if (isset($atts['zoom_position'])){
          $html .= " data-zoom-position='".$atts['zoom_position']."'";
        }
      }

      $html .= "/></a>";
    }
  }

  // start item meta data
  $img_metadata = "";
  if (isset($atts['metadata'])){
    $metadata = explode(",",$atts['metadata']);
    foreach($metadata as $field){
      $this_field = $data->mods->$field;
      if (isset($this_field)){
        if (is_array($this_field)){
          foreach($this_field as $field_val){
            $img_metadata .= $field_val . "<br/>";
          }
        } else {
          if (isset($this_field[0])){
            $img_metadata .= $this_field[0] . "<br/>";
          }
        }
      }
    }
    $html .= "<div class='wp-caption-text drstk-caption'";
    if (isset($atts['caption-align'])){
      $html .= " data-caption-align='".$atts['caption-align']."'";
    }
    if (isset($atts['caption-position'])){
      $html .= " data-caption-position='".$atts['caption-position']."'";
    }
    $html .= "><a href='".drstk_home_url()."item/".$atts['id']."'>".$img_metadata."</a></div>";
  }

  // start hidden fields
  $html .= "<div class=\"hidden\">";
  foreach($data as $field => $val){
    if (is_array($val)){
      foreach($val as $field_val){
        $html .= $field_val . "<br/>";
      }
    } elseif(is_object($val)){
      // do nothing with objects
    } else {
      $html .= $val . "<br/>";
    }
  }
  $html .= "</div></div>";
  $cache_output = $html;
  $cache_time = 1000;
  set_transient(md5('DRSTK'.serialize($atts)) , $cache_output, $cache_time * 60);
  return $html;
}

add_action( 'wp_ajax_get_item_solr_admin', 'item_solr_admin_ajax_handler' ); //for auth users

function item_solr_admin_ajax_handler() {
  $data = array();
  // Handle the ajax request
  check_ajax_referer( 'item_admin_nonce' );
  $url = drstk_api_url("drs", $_POST['pid'], "files", NULL, "solr_only=true");
  $response = get_response($url);
  $data = json_decode($response['output']);
  wp_send_json(json_encode($data));
  wp_die();
}

function drstk_item_shortcode_scripts() {
  global $post, $wp_query;
  if( is_a( $post, 'WP_Post' ) && (has_shortcode( $post->post_content, 'drstk_item') || has_shortcode( $post->post_content, 'drstk_single')) && !isset($wp_query->query_vars['drstk_template_type']) ) {
    wp_register_script('drstk_elevatezoom', DRS_PLUGIN_URL.'/assets/js/elevatezoom/jquery.elevateZoom-3.0.8.min.js', array( 'jquery' ));
    wp_enqueue_script('drstk_elevatezoom');
    wp_register_script( 'drstk_zoom', DRS_PLUGIN_URL . '/assets/js/zoom.js', array( 'jquery' ));
    wp_enqueue_script('drstk_zoom');
    wp_register_script('drstk_cdn_jwplayer', 'https://content.jwplatform.com/libraries/dTFl0VEe.js');
    wp_enqueue_script('drstk_cdn_jwplayer');
  }
}

function check_master($master){
  // Create a cURL handle
  $ch = curl_init($master);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  // Execute
  curl_exec($ch);
  // Check HTTP status code
  if (!curl_errno($ch)) {
    switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
      case 200:  # OK
        return true;
        break;
      default:
        return false;
    }
  }
  curl_close($ch);
}
add_action( 'wp_enqueue_scripts', 'drstk_item_shortcode_scripts');
