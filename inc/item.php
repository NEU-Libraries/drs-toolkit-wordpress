<?php
global $item_pid, $data, $collection, $repo, $all_meta_options, $full_pid, $title;
$collection = drstk_get_pid();
$errors = drstk_get_errors();
$meta_options = get_option('drstk_item_page_metadata');
if (!is_array($meta_options)){
  $meta_options = NULL;
}
$assoc_meta_options = drstk_get_assoc_meta_options();

function get_item_details($data, $assoc=false){
  global $repo, $meta_options, $assoc_meta_options;
  $errors = drstk_get_errors();
  if (check_for_bad_data($data)){
    return false;
  }
  $html = '';
  if ($repo == "wp"){
    $abs = "Abstract/Description";
    $data->mods->$abs = $data->post_content;
    $datec = "Date created";
    $data->mods->$datec = $data->post_date;
  }
  if (isset($data->mods)){ //mods
    $html .= parse_metadata($data->mods, $html);
  } else if (isset($data->_source)){//solr_only = true
    if ($assoc == 1){
      $html .= parse_metadata($data->_source, $html, true, false, $assoc_meta_options);
    } else {
      $html .= parse_metadata($data->_source, $html, true);
    }
  }
  if ($repo == "dpla"){
    $html = parse_metadata($data, "", false, true);
  }
  $niec_facets = get_option('drstk_niec_metadata');
  $niec_facets_to_display = array();
  if (is_array($niec_facets)){
    foreach($niec_facets as $facet){
      $niec_facets_to_display[$facet] = drstk_get_facet_name($facet, true);
    }
  }
  if (get_option('drstk_niec') == 'on' && count($niec_facets_to_display) > 0 && isset($data->niec)){
    $html = parse_metadata($data->niec, $html, false, false, $niec_facets_to_display);
  }
  return $html;
}

function parse_metadata($data, $html, $solr=false, $dpla=false, $special_options=NULL){
  global $meta_options, $assoc_meta_options;
  if ($special_options != NULL){
    $temp_meta_options = $special_options;
  } else {
    $temp_meta_options = $meta_options;
  }
  if ($solr){//this is necessary to not use default solr ordering
    $arr1 = (array) $data;
    $arr2 = $temp_meta_options;
    $data = array();
    foreach ($arr2 as $key=>$val) {
      $data[$val] = $arr1[$val];
    }
  }
  if ($dpla){
    $data = map_dpla_to_mods($data);
  }
  if ($temp_meta_options != NULL){
    $data = (array) $data;
    foreach($temp_meta_options as $key => $val){
      $value = NULL;
      if (isset($data->$val)){
        $value = $data->$val;
      } else if (isset($data[$val])){
        $value = $data[$val];
      }
      if ($value == NULL){
        continue;//skip to next in each loop
      }
      $html .= "<div class='drs-field-label'><b>";
      if (!isset($temp_meta_options[$key])){
        $html .= titleize($key);
      } else {
        if (strpos($temp_meta_options[$key], '_') !== false) {
          $html .= titleize($temp_meta_options[$key]);
        } else {
          $html .= $temp_meta_options[$key];
        }
      }
      $html .= "</b></div><div class='drs-field-value'>";
      if (is_array($value)){
        for ($i =0; $i<count($value); $i++){

          $fieldValue = $value[$i];
          // @TODO this is a band aid. There's lots to figure out about how
          // this actually works with the data sources, especially DPLA
          // see https://github.com/NEU-Libraries/drs-toolkit-wp-plugin/issues/166
          if (is_array($fieldValue)) {
            $fieldValue = $fieldValue[0];
          }
          if (substr($fieldValue, 0, 4) == "http"){
            $html .= '<a href="'.$fieldValue.'" target="_blank">'.$fieldValue.'</a>';
          } elseif ((strpos($fieldValue, 'Read Online') !== false) && $key == "Location") {
            $html .= $fieldValue;
          } else {
            $string = $fieldValue;

            $link_pattern = "/(?i)\\b(?:https?:\\/\\/|www\\d{0,3}[.]|[a-z0-9.\\-]+[.][a-z]{2,4}\\/)(?:[^\\s()<>]+|\\([^\\s()<>]+|\\([^\\s()<>]+\\)*\\))+(?:\\([^\\s()<>]+|\\([^\\s()<>]+\\)*\\)|[^\\s`!()\\[\\]{};:'\".,<>?«»“”‘’])/i";
            $email_pattern = "/[A-Z0-9_\\.%\\+\\-\\']+@(?:[A-Z0-9\\-]+\\.)+(?:[A-Z]{2,4}|museum|travel)/i";
            preg_match_all($link_pattern, $string, $link_matches);
            preg_match_all($email_pattern, $string, $email_matches);
            foreach($link_matches as $match){
              if (count($match) > 0) {
                $string = str_ireplace($match[0], "<a href='".$match[0]."'>".$match[0]."</a>", $string);
              }
            }
            foreach($email_matches as $match){
              if (count($match) > 0) {
                $string = str_ireplace($match[0], "<a href='mailto:".$match[0]."'>".$match[0]."</a>", $string);
              }
            }
            if (titleize($key) == "Table of contents" || $temp_meta_options[$key] == "Table of contents"){
              $string = str_replace("--", "<br/>", $string);
            }
            $html .= $string;
          }
          if ($i != count($value)-1){
            $html .= "<br/> ";
          }
        }
      } else {
        $html .= $value;
      }
      $html .= "</div>";
    }
  } else {
    foreach($data as $key => $value){
      if (($temp_meta_options == NULL) || array_key_exists($key, $temp_meta_options) || in_array($key, $temp_meta_options)){
        $html .= "<div class='drs-field-label'><b>";
        if (!isset($temp_meta_options[$key])){
          $html .= titleize($key);
        } else {
          $html .= $temp_meta_options[$key];
        }
        $html .= "</b></div><div class='drs-field-value'>";
        if (is_array($value)){
          for ($i =0; $i<count($value); $i++){
            if (substr($value[$i], 0, 4) == "http"){
              $html .= '<a href="'.$value[$i].'" target="_blank">'.$value[$i].'</a>';
            } elseif ((strpos($value[$i], 'Read Online') !== false) && $key == "Location") {
              $html .= $value[$i];
            } else {
              $string = $value[$i];
              $link_pattern = "/(?i)\\b(?:https?:\\/\\/|www\\d{0,3}[.]|[a-z0-9.\\-]+[.][a-z]{2,4}\\/)(?:[^\\s()<>]+|\\([^\\s()<>]+|\\([^\\s()<>]+\\)*\\))+(?:\\([^\\s()<>]+|\\([^\\s()<>]+\\)*\\)|[^\\s`!()\\[\\]{};:'\".,<>?«»“”‘’])/i";
              $email_pattern = "/[A-Z0-9_\\.%\\+\\-\\']+@(?:[A-Z0-9\\-]+\\.)+(?:[A-Z]{2,4}|museum|travel)/i";
              preg_match_all($link_pattern, $string, $link_matches);
              preg_match_all($email_pattern, $string, $email_matches);
              foreach($link_matches as $match){
                if (count($match) > 0) {
                  $string = str_ireplace($match[0], "<a href='".$match[0]."'>".$match[0]."</a>", $string);
                }
              }
              foreach($email_matches as $match){
                if (count($match) > 0) {
                  $string = str_ireplace($match[0], "<a href='mailto:".$match[0]."'>".$match[0]."</a>", $string);
                }
              }
              $html .= $string;
            }
            if ($i != count($value)-1){
              $html .= "<br/> ";
            }
          }
        } else {
          $html .= $value;
        }
        $html .= "</div>";
      }
    }
  }
  return $html;
}

function get_download_links(){
  global $data;
  if (check_for_bad_data($data)){
    return false;
  }
  if (isset($data->content_objects)){
    echo "<br/><h4>Downloads</h4>";
  } else {
    $data->content_objects = new StdClass;
  }
  foreach($data->content_objects as $key=>$val){
    if ($val != "Thumbnail Image"){
      if (is_user_logged_in() && drstk_api_auth_enabled()){
        $content_pid = explode("/", $key);
        $content_pid = end($content_pid);
        $content_pid = str_replace("?datastream_id=content","",$content_pid);
        echo " <a href='".drstk_home_url()."download/".$content_pid."' class='themebutton button btn' data-label='download' data-pid='".$data->pid."'>".$val."</a> ";
      } else {
        echo " <a href='".$key."' target='_blank' class='themebutton button btn' data-label='download' data-pid='".$data->pid."'>".$val."</a> ";
      }
    }
  }
}

function get_item_title(){
  global $item_pid, $data, $url, $repo, $full_pid, $title;
  $repo = drstk_get_repo_from_pid($item_pid);
  if ($repo == "drs"){
    $url = drstk_api_url("drs", $item_pid, "files");
    $response = get_response($url);
    $data = json_decode($response['output']);
    if (check_for_bad_data($data)){
      return false;
    }
    $title = $data->mods->Title[0];
    echo $title;
  } else if ($repo == "dpla"){
    $full_pid = $item_pid;
    $item_pid = explode(":",$item_pid);
    $item_pid = $item_pid[1];
    $url = drstk_api_url("dpla", $item_pid, "items");
    $response = get_response($url);
    $data = json_decode($response['output']);
    if (check_for_bad_data($data)){
      return false;
    }
    $data->mods = new StdClass;
    if (is_array($data->docs[0]->sourceResource->title)){
      $title = $data->docs[0]->sourceResource->title;
    } else {
      $title = array($data->docs[0]->sourceResource->title);
    }
    $data->mods->Title = $title;
    $title = str_replace('"','\"', $title);
    $title = $title[0];
    echo $title;
  } else if ($repo == "wp"){
    $full_pid = $item_pid;
    $item_pid = explode(":",$item_pid);
    $item_pid = $item_pid[1];
    $data = get_post($item_pid);
    $data->mods = new StdClass;
    $data->mods->Title = array($data->post_title);
    $title = $data->post_title;
    echo $title;
  }
}

function get_item_breadcrumbs(){
  global $item_pid, $data, $breadcrumb_html, $collection, $title;
  if (check_for_bad_data($data)){
    return false;
  }
  $breadcrumb_html = array();
  $end = false;
  if (isset($data->breadcrumbs)){
    $breadcrumbs = $data->breadcrumbs;
  } else {
    $breadcrumbs = new StdClass;
  }
  if (isset($collection,$breadcrumbs)){
    foreach($breadcrumbs as $pid=>$this_title){
      if ($pid == $item_pid){
        $breadcrumb_html[]= "<a href='".drstk_home_url()."item/".$pid."'> ".$this_title."</a>";
      } else if ($pid == $collection){
        $breadcrumb_html[]= "<a href='".drstk_home_url()."browse'>Browse</a>";
        $end = true;
      } else if ($end == true) {
      } else {
        $breadcrumb_html[]= "<a href='".drstk_home_url()."collection/".$pid."'> ".$this_title."</a>";
      }
    }
  } else {
    $breadcrumb_html[]= "<a href='".drstk_home_url()."item/".$item_pid."'> ".$title."</a>";
    $breadcrumb_html[] = "<a href='".drstk_home_url()."browse'>Browse</a>";
  }
  echo implode(" > ", array_reverse($breadcrumb_html));
}

function get_item_image(){
  global $item_pid, $data, $repo;
  $errors = drstk_get_errors();
  if (check_for_bad_data($data)){
    echo check_for_bad_data($data);
    return false;
  }
  if ($repo == "dpla"){
    if (isset($data->docs[0]->object)){
      $img = $data->docs[0]->object;
    } else {
      $img = DPLA_FALLBACK_IMAGE_URL;
    } //not doing canonical object because we can't do any zoom or media playing anyway
  }
  if ($repo == "wp"){
    $meta = wp_get_attachment_metadata($item_pid); //get sizes
    $data->canonical_object = new StdClass;
    $url = $data->guid;
    if (strpos($data->post_mime_type, "audio") !== false){
      $type = "Audio File";
    } else if (strpos($data->post_mime_type, "video") !== false){
      $type = "Video File";
    } else {
      $type = "Master Image";
      $meta = wp_get_attachment_metadata($item_pid); //get sizes
      $thumb_base = wp_get_attachment_thumb_url($item_pid);
      if (isset($meta['sizes'])){
        $thumb_base = explode("/",$thumb_base);
        $arr = array_pop($thumb_base);
        $thumb_base = implode("/", $thumb_base);
        if (isset($meta['sizes']['large'])){
          $img = $thumb_base."/".$meta['sizes']['large']['file'];
        } else {
          $img = $thumb_base."/".$meta['sizes']['medium']['file'];
        }
      }
    }
    $data->canonical_object->$url = $type;
  }
  if (isset($data->thumbnails)){
    $img = $data->thumbnails[count($data->thumbnails)-2];
  }
  if (isset($data->page_objects)){
   $pages = $data->page_objects;
   if (count($pages) > 0){
     $gallery_html = '<div class="carousel slide" id="single_carousel">';
     $img_html = "";
     $i = 0;
     foreach($pages as $img=>$ordinal_value){
       $img_html .= "<div class='item";
       if ($i == 0){
         $img_html .= " active";
       }
       $img_html .= "'><a href='' data-toggle='modal' data-target='#drs_item_modal' class='drs_page_image' data-img='".$img."' data-ordinal_value='".$ordinal_value."'><img";
       if ($i == 0){
         $img_html .= " src='".$img."'";
       } else {
         $img_html .= " data-src='".$img."'";
       }
       $img_html .= "/></a><div class='carousel-caption'><a href='' data-toggle='modal' data-target='drs_item_modal' class='drs_item_modal' data-img='".$img."' data-ordinal_value='".$ordinal_value."'>Page ".$ordinal_value."</a></div></div>";
       $i++;
     }
     $gallery_html .= '<div class="carousel-inner">'.$img_html.'</div>';
     $gallery_html .= '<a class="left carousel-control" href="#single_carousel" role="button" data-slide="prev"><i class="glyphicon-chevron-left fa fa-chevron-left" aria-hidden="true"></i><span class="sr-only">Previous</span></a><a class="right carousel-control" href="#single_carousel" role="button" data-slide="next"><i class="glyphicon-chevron-right fa fa-chevron-right" aria-hidden="true"></i><span class="sr-only">Next</span></a>';
     $gallery_html .= '</div>';
     $gallery_html .= '<div class="modal fade" id="drs_item_modal"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button><h4 class="modal-title">Page Images</h4></div><div class="modal-body"><nav class="pagination"><ul class="pagination"><li><a href="#" class="drs_page_image prev"><span class="fa fa-chevron-left"></span></a></li>';
     foreach($pages as $img=>$ordinal_value){
       $gallery_html .= "<li><a href='#' class='drs_page_image' data-img='".$img."' data-ordinal_value='".$ordinal_value."'>".$ordinal_value."</a></li>";
     }
     $gallery_html .= '<li><a href="#" class="drs_page_image next"><span class="fa fa-chevron-right"></span></a></li></ul></nav><div class="body"></div></div><div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Close</button></div></div><!-- /.modal-content --></div><!-- /.modal-dialog --></div><!-- /.modal -->';
     echo $gallery_html;
   }
 } else if (isset($data->canonical_object)){
    $val = $data->canonical_object;
    $key = $data->canonical_object;
    if ($val == 'Master Image'){
      if ($repo == "wp"){
        $zoom_img = $data->guid;
      } else {
        $zoom_img = $data->thumbnails[count($data->thumbnails)-1];
      }
      echo  '<img id="drs-item-img" src="'.$img.'" data-zoom-image="'.$zoom_img.'"/>';
      echo '<script type="text/javascript"> jQuery("#drs-item-img").elevateZoom();</script>';
    } else if ($val == 'PDF'){
      if (isset($data->mods->Location) && strpos($data->mods->Location[0], "issuu") !== FALSE){
        $location_href = explode("'", strval(htmlentities($data->mods->Location[0])));
        if (count($location_href) == 1){
          $location_href = explode('"', strval(htmlentities($data->mods->Location[0])));
        }
        $issu_id = explode('?',$location_href[1]);
        $issu_id = explode('=',$issu_id[1]);
        $issu_id = $issu_id[1];
        echo '<div data-configid="'.$issu_id.'" style="width:100%; height:500px;" class="issuuembed"></div><script type="text/javascript" src="//e.issuu.com/embed.js" async="true"></script>';
      } else {
        echo  '<img id="drs-item-img" src="'.$img.'" />';
      }
    } else if ($val == 'Video File' || $val == 'Audio File'){
      if ($repo == "wp"){
        print(do_shortcode('[video src="'.$data->guid.'"]'));
      } else {
        print(insert_jwplayer($key, $val, $data, $img));
      }
    } else {
      //nothing special so lets just show the thumbnail
      echo  '<img id="drs-item-img" src="'.$img.'" />';
    }
  } else {
    //case where there is no canonical_objects set
    echo  '<img id="drs-item-img" src="'.$img.'" />';
  }
}

function get_associated_files(){
  global $data, $assoc_meta_options;
  $errors = drstk_get_errors();
  if (isset($data->associated) && ($data->associated != NULL) && (get_option('drstk_assoc') == 'on')){
    $associated_html = '';
    $title = (get_option('drstk_assoc_title') != '') ? get_option('drstk_assoc_title') : 'Associated Files';
    $associated_html .= "<div class='panel panel-default assoc_files'><div class='panel-heading'>".$title."</div><div class='panel-body'>";
      $assoc_pid = key(get_object_vars($data->associated)); //using this just to get the first title
    $assoc_title = $data->associated->$assoc_pid; //using this just to get the first title
    $url = drstk_api_url("drs", $assoc_pid, "files", NULL, "solr_only=true");
    $response = get_response($url);
    $assoc_data = json_decode($response['output']);
    if (check_for_bad_data($assoc_data)){
      return false;
    } else {
      if (isset($assoc_data->_source->fields_thumbnail_list_tesim)){
        $associated_html .= "<a href='".drstk_home_url()."item/".$assoc_data->_source->id."'><img src='https://repository.library.northeastern.edu".$assoc_data->_source->fields_thumbnail_list_tesim[1]."'/></a>";
      }
      $assoc = true;
      $associated_html .= get_item_details($assoc_data, $assoc);
    }
    if (count(get_object_vars($data->associated)) > 1){
      $pids = array_keys(get_object_vars($data->associated));
      $associated_html .= "<a href='' class='button associated-next btn-sm' data-pid='".$pids[1]."' data-all_pids='".implode(",", $pids)."'>Next</a>";
    }
    $associated_html .= "</div></div>";
    echo $associated_html;
  }
}

function get_related_content(){
  global $wp_query, $post, $item_pid;
  $title = (get_option('drstk_appears_title') != "") ? get_option('drstk_appears_title') : "Item Appears In";
  if (get_option('drstk_appears') == 'on'){
    $pidnum = explode(":", $item_pid);
    if (count($pidnum) > 0){
      if (count($pidnum) > 1){
        $pidnum = $pidnum[1];
      } else {
        $pidnum = "dpla:".$item_pid;
      }
      echo '<div class="panel panel-default related_content"><div class="panel-heading">'.$title.'</div><div class="panel-body">';
      do_related_content_query($pidnum, 1);
      echo "</div></div>";
    }
  }
}

function do_related_content_query($pid, $paged){
  global $wp_query, $post;
  $query_args = array( 's' => $pid, 'post_type'=>array('post', 'page'), 'posts_per_page'=>2, 'paged'=>$paged, 'post_status'=>'publish');
  $wp_query = new WP_Query( $query_args );

  $rel_query = relevanssi_do_query($wp_query);
  if (count($rel_query) > 0){
    foreach($rel_query as $r_post){
      $post = $r_post;
      $the_post = $post;
      get_template_part( 'content', 'excerpt' );
    }
    if (count($rel_query) > 2){
      echo the_posts_pagination( array( 'mid_size'  => 2 ) );
    }
  } else {
    //no related content
  }
  wp_reset_postdata();
}


add_action( 'wp_ajax_get_related_content_paginated', 'related_content_paginated_handler' ); //for auth users
add_action( 'wp_ajax_nopriv_get_related_content_paginated', 'related_content_paginated_handler' ); //for nonauth users
function related_content_paginated_handler(){
  global $post;
  $errors = drstk_get_errors();
  if (isset($_GET['pid']) && isset($_GET['page']) && $_GET['page'] != null && $_GET['pid'] != NULL){
    $pid = $_GET['pid'];
    $page = intval($_GET['page']);
    do_related_content_query($pid, $page);
  }
  die();
}

function check_for_bad_data($data){
  $errors = drstk_get_errors();;
  if ($data == null) {
    return $errors['item']['fail'];
  } else if (isset($data->error)) {
    return $errors['item']['no_results'];
  }
}

function insert_jwplayer($av_pid, $canonical_object_type, $data, $drs_item_img) {
  $errors = drstk_get_errors();
  $av_type = "";
  if ($canonical_object_type == 'Video File'){
    $av_provider = 'video';
    $av_type = "MP4";
  }
  if ($canonical_object_type == 'Audio File'){
    $av_provider = 'sound';
    $av_type = "MP3";
  }

  if (strpos($av_pid, "repository.library.northeastern.edu") !== false){
    $av_pid = explode("/", $av_pid);
    $av_pid = end($av_pid);
    $av_pid = str_replace("?datastream_id=content","",$av_pid);
    if (isset($data->thumbnails)){
      $av_poster = $data->thumbnails[3];
    }
    $numeric_pid = str_replace(":", "-", $av_pid);
    $id_img = 'drs-item-img-'.$numeric_pid;
    $id_video = 'drs-item-video-'.$numeric_pid;
  } else {
    $id_img = 'drs-item-img-'.$data->id;
    $id_video = 'drs-item-video-'.$data->id;
  }

  if (!isset($av_poster)){
    $av_poster = $drs_item_img;
  }
  $user_agent = $_SERVER['HTTP_USER_AGENT'];
  $html = '<img id="'.$id_img.'" src="'.$drs_item_img.'" class="replace_thumbs"/>';
  $html .= '<div id="'.$id_video.'"></div>';
  $html .= '<script type="text/javascript">
  jwplayer.key="' . JWPLAYER_KEY . '";
  var primary = "html5";
  var provider = "'.$av_provider.'";
  var is_chrome = navigator.userAgent.indexOf(\'Chrome\') > -1;
  var is_safari = navigator.userAgent.indexOf("Safari") > -1;
  if ((is_chrome)&&(is_safari)) {is_safari=false;}
  jQuery(document).ready(function($){
  $("#'.$id_img.'").hide();
  jwplayer("'.$id_video.'").setup({';
  $html .= 'width: "100%",
        height: 400,
        rtmp: {bufferlength: 5},';
  if ($av_poster != null){$html .= 'image: "'.$av_poster.'",';}
  $html .= 'provider: "'.$av_provider.'",
    androidhls: "true",
    primary: primary,
    hlshtml: "true",
    aspectratio: "16:9",';
  if (strpos($av_pid, "neu") !== false) {
    $html .='sources:
    [{ file: "https://repository.library.northeastern.edu/wowza/'.$av_pid.'/plain", type:"'.strtolower($av_type).'"},';
    $html .= '{ file: "https://repository.library.northeastern.edu/wowza/'.$av_pid.'/playlist.m3u8"}
    ],';
  } else {
    $html .= 'sources:[{file:"'.$av_pid.'"}],';
  }
  $html .= '
  });
  jwplayer("'.$id_video.'").on("ready", function() {
   if (is_safari){
    // Set poster image for video element to avoid black background for audio-only programs.
    $("'.$id_video.' video").attr("poster", "'.$av_poster.'");
   }
  });
  function errorMessage() {
    $("#'.$id_img.'").before("<div class=\'alert alert-warning\'>'.$errors['item']['jwplayer_fail'].'<br /><strong>Error Message:</strong> "+e.message+"</div>");
    $("#'.$id_img.'").show();
    $("#'.$id_video.'").hide();
  }
  jwplayer("'.$id_video.'").on(\'error\', function(){
    errorMessage();
  });
  jwplayer("'.$id_video.'").on(\'setupError\', function(){
    errorMessage();
  });
  jwplayer("'.$id_video.'").on(\'buffer\', function() {
    theTimeout = setTimeout(function(e) {
      errorMessage(e);
    }, 5000);
  });
  jwplayer("'.$id_video.'").on("play", function(){
     clearTimeout(theTimeout);
   });
   $(".replace_thumbs").click(function() {
     jwplayer("'.$id_video.'").play()
   })
  });</script>';

  return $html;
}

function map_dpla_to_mods($data){
  global $all_meta_options, $meta_options;
  $sourceResource = $data->docs[0]->sourceResource;

  if (isset($sourceResource->creator)){
    $data->mods->Creator = $sourceResource->creator;
  }
  if (isset($sourceResource->contributor)){
    $data->mods->Contributor = $sourceResource->contributor;
  }
  if (isset($sourceResource->publisher)){
    $data->mods->Publisher = $sourceResource->publisher;
  }
  if(isset($sourceResource->date)){
    if(isset($sourceResource->date->displayDate)){
      $datec = "Date created";
      $data->mods->$datec = $sourceResource->date->displayDate;
    }
  }
  $type = "Type of Resource";
  if (isset($sourceResource->type)){
    $data->mods->$type = "";
    $data->mods->$type = $sourceResource->type;
  }
  if (isset($sourceResource->description)){
    $absname = "Abstract/Description";
    $data->mods->$absname = implode("<br/>",$sourceResource->description);
  }
  if (isset($sourceResource->subject)){
    $subjname = "Subjects and keywords";
    $data->mods->$subjname = array();
    foreach($sourceResource->subject as $key=>$val){
      array_push($data->mods->$subjname, $val->name);
    }
  }
  if (isset($sourceResource->format)){
    $data->mods->Format = $sourceResource->format;
  }
  if (isset($sourceResource->language)){
    $data->mods->Language = array();
    foreach($sourceResource->language as $key=>$val){
      array_push($data->mods->Language, $val->name);
    }
  }
  $relname = "Related item";
  if (isset($sourceResource->relation)){
    $data->mods->$relname = array();
    array_push($data->mods->$relname, $sourceResource->relation);
  }
  if (isset($sourceResource->rights)){
    $data->mods->Rights = $sourceResource->rights;
  }
  $permname = "Permanent URL";
  $data->mods->$permname = is_array($data->docs[0]->isShownAt) ? $data->docs[0]->isShownAt : array($data->docs[0]->isShownAt);
  if(isset($sourceResource->identifier)){
    $data->mods->Identifier = $sourceResource->identifier;
  }


  //FIELDS not connected because they would have to come from the originalRecord which has incredibly unreliable JSON formatting
  // Location, date issued, copyright date, table of contents, notes, genre, phsyical description
  return $data->mods;
}

add_action( 'wp_ajax_get_associated_item', 'associated_ajax_handler' ); //for auth users
add_action( 'wp_ajax_nopriv_get_associated_item', 'associated_ajax_handler' ); //for nonauth users
function associated_ajax_handler() {
  // Handle the ajax request
  global $assoc_meta_options;
  $errors = drstk_get_errors();
  check_ajax_referer( 'item_drs' );
  if (isset($_POST['pid']) && ($_POST['pid'] != NULL) && (get_option('drstk_assoc') == 'on')){
    $associated_html = '';
    $title = (get_option('drstk_assoc_title') != '') ? get_option('drstk_assoc_title') : 'Associated Files';
    $associated_html .= "";
    $assoc_pid = $_POST['pid']; //using this just to get the first title
    $url = drstk_api_url("drs", $assoc_pid, "files", NULL, "solr_only=true");
    $response = get_response($url);
    $assoc_data = json_decode($response['output']);
    if (check_for_bad_data($assoc_data)){
      return false;
    } else {
      if (isset($assoc_data->_source->fields_thumbnail_list_tesim)){
        $associated_html .= "<a href='".drstk_home_url()."item/".$assoc_data->_source->id."'><img src='https://repository.library.northeastern.edu".$assoc_data->_source->fields_thumbnail_list_tesim[1]."'/></a>";
      }
      $assoc = true;
      $associated_html .= get_item_details($assoc_data, $assoc);
    }
    if (isset($_POST['all_pids'])){
      $all_pids = explode(",",$_POST['all_pids']);
      $key = array_search($assoc_pid, $all_pids);
      if ($key > 0){
        $associated_html .= "<a href='' class='button associated-prev btn-sm' data-pid='".$all_pids[$key-1]."' data-all_pids='".$_POST['all_pids']."'>Previous</a>";
      }
      if ($key == 0 || $key != (count($all_pids)-1)){
        $associated_html .= "<a href='' class='button associated-next btn-sm' data-pid='".$all_pids[$key+1]."' data-all_pids='".$_POST['all_pids']."'>Next</a>";
      }
    }
    $data = array('html'=>$associated_html);
  } else {
    $data = array('error'=>"There was an error retrieving the associated file. Please try again.");
  }
  wp_send_json(json_encode($data));
  wp_die();
}

/**
 * Get a value of data from the associated content for an item, as
 * declared on the Item Pages Custom Text post
 *
 * Essentially a rework of the older get_item_extension function, which only returned
 * the post's content, not the post itself.
 *
 * @param string $data The data to return, one of 'content', 'placement'
 */

function drstk_get_custom_content_data($data) {
  global $post, $item_pid, $repo, $full_pid;
  if ($repo != "drs"){
    $pid = $full_pid;
  } else {
    $pid = $item_pid;
  }
  $args = array(
      'post_type' => 'drstk_item_extension',
      'posts_per_page' => 1,
      'post_status' => 'publish',
      'meta_query' => array(
          array(
              'key'     => 'item-id',
              'value'   => $pid,
              'compare' => '='
          )
      )
  );
  $meta_query = new WP_Query( $args );
  // WP always assumes "THE LOOP", so this works around on the assumption that there is
  // only one custom text page for each item. No real reason for that assumption
  // @TODO: decide whether to remove that assumption
  // @TODO: can I get rid of the first `if`?
  if ( $meta_query->have_posts() ) {
    while ( $meta_query->have_posts() ) {
      $meta_query->the_post();
      $post_id = $post->ID;

      switch ($data) {
        case 'content':
          $content = get_the_content();
          drstk_item_shortcode_scripts();
          drstk_map_shortcode_scripts();
          drstk_gallery_shortcode_scripts();
          drstk_tile_shortcode_scripts();
          drstk_timeline_shortcode_scripts();
          $panels_data = get_post_meta( $post->ID, 'panels_data', true );
          if ( !empty( $panels_data ) ) {
            $content = siteorigin_panels_render($post_id);
          }
          $content = do_shortcode($content);
          wp_reset_postdata();
          return $content;
          break;

        case 'placement':
          $placement = get_post_meta( $post->ID, 'drstk-custom-content-placement', true);
          wp_reset_postdata();
          return $placement;
          break;

        default:
          wp_reset_postdata();
          return '';
      }
    }
  } else {
    wp_reset_postdata();
    return '';
  }
}
