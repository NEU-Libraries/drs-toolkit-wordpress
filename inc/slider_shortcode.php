<?php
/* adds shortcode */
add_shortcode( 'drstk_gallery', 'drstk_gallery' );
add_shortcode('drstk_slider', 'drstk_gallery');
function drstk_gallery( $atts ){
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

  if (isset($atts['id'])){
    $images = array_map('trim', explode(',', $atts['id']));
    $img_html = '';
    $height = $width = 0;
    $i = 0;
    if (isset($atts['image-size'])){
      $num = $atts['image-size']-1;
    } else {
      $num = 4;
    }
    if (isset($atts['transition']) && $atts['transition'] == 'slide'){
      $transition_class = "";
    } elseif (isset($atts['transition']) && $atts['transition'] == 'fade'){
      $transition_class = "carousel-fade";
    } else {
      $transition_class = "";
    }
   foreach($images as $id){
     $repo = drstk_get_repo_from_pid($id);
     if ($repo != "drs"){$pid = explode(":",$id); $pid = $pid[1];} else {$pid = $id;}
     if ($repo == "drs"){
       $url = drstk_api_url("drs", $id, "files", NULL, "solr_only=true");
       $response = get_response($url);

       $data = json_decode($response['output']);

       $data = $data->_source;
       $thumbnail = "https://repository.library.northeastern.edu".$data->fields_thumbnail_list_tesim[$num];
     }

     if ($repo == "wp"){
       $post = get_post($pid);
       $data = new StdClass;
       $meta = wp_get_attachment_metadata($pid); //get sizes
       $thumb_base = wp_get_attachment_thumb_url($pid);
       $thumb_base = explode("/",$thumb_base);
       $arr = array_pop($thumb_base);
       $thumb_base = implode("/", $thumb_base);

       // @TODO $num refers to the image size from the settings tab. a problem area for refactoring
       switch ($num) {
         case 1:
           $thumbnail = $thumb_base."/".$meta['sizes']['thumbnail']['file'];
           break;

         case 2:
         case 3:
           // small uploads don't get a medium derivative, so provide a fallback to thumbnail
           if (isset($meta['sizes']['medium'])) {
             $thumbnail = $thumb_base."/".$meta['sizes']['medium']['file'];
           } else {
             $thumbnail = $thumb_base."/".$meta['sizes']['thumbnail']['file'];
           }
           break;

         case 4:
           if (isset($meta['sizes']['large'])){
             $thumbnail = $thumb_base."/".$meta['sizes']['large']['file'];
           } else {
             $thumbnail = drstk_home_url()."wp-content/uploads/".$meta['file'];
           }
           break;

         case 5:
           if (isset($meta['sizes']['large'])){
             $thumbnail = $thumb_base."/".$meta['sizes']['large']['file'];
           } else {
             $thumbnail = drstk_home_url()."wp-content/uploads/".$meta['file'];
           }
           break;
       }

       $master = $post->guid;
       $data->full_title_ssi = $post->post_title;
       $data->abstract_tesim = array($post->post_excerpt);
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
       $data->full_title_ssi = $title;
       $data->abstract_tesim = array($description);
       if (isset($dpla->docs[0]->sourceResource->creator)){
         $data->creator_tesim = is_array($dpla->docs[0]->sourceResource->creator) ? $dpla->docs[0]->sourceResource->creator : array($dpla->docs[0]->sourceResource->creator);

       }
       if (isset ($dpla->docs[0]->sourceResource->date)) {
         $data->date_ssi = $dpla->docs[0]->sourceResource->date->displayDate;
       }

     }
       if (!isset($data->error)){
        $pid = $id;
         $this_height = getimagesize($thumbnail);
         $this_height = $this_height[1];
         if ($this_height > $height){
           $height = $this_height;
         }
         $this_width = getimagesize($thumbnail);
         $this_width = $this_width[0];
         if ($this_width > $width){
           $width = $this_width;
         }
        $title = $data->full_title_ssi;
        if (is_array($title)){
          $title = $title[0];
        }
         $img_html .= "<div class='item";
         if ($i == 0){
           $img_html .= " active";
         }
         $img_html .= "'><a href='".drstk_home_url()."item/".$pid."'><img";
         if ($i == 0){
           $img_html .= " src='".$thumbnail."'";
         } else {
           $img_html .= " data-src='".$thumbnail."'";
         }
         $img_html .= " data-height='".$this_height."'";
         $img_html .= "  alt='".$title."'></a>";
         if (isset($atts['caption']) && $atts['caption'] == "on"){
           $img_metadata = "";
           if (isset($atts['metadata'])){
             $metadata = explode(",",$atts['metadata']);
             foreach($metadata as $field){
                if (isset($data->$field)){
                  $this_field = $data->$field;
                 if (isset($this_field)){
                   if (is_array($this_field)){
                     foreach($this_field as $val){
                       if (is_array($val)){
                        $img_metadata .= implode("<br/>",$val)."<br/>";
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
           $img_html .= "<div class='carousel-caption'";
           if (isset($atts['caption-align']) || isset($atts['caption-position'])){
             $img_html .= "style='";
             if (isset($atts['caption-align'])){
               $img_html .= "text-align:".$atts['caption-align'];
             }
             if (isset($atts['caption-position'])){
               $img_html .= "; position:".$atts['caption-position'];
             }
             if (isset($atts['caption-width']) && $atts['caption-width'] == "100%"){
               $img_html .= "; width:".$atts['caption-width'];
             }
             $img_html .= "'";
           }
           if (isset($atts['caption-width']) && $atts['caption-width'] != "100%"){
             $img_html .= " data-caption-width='image'";
           }
           $img_html .= "><a href='".drstk_home_url()."item/".$pid."'>".$img_metadata."</a></div>";
           $img_html .= "<div class=\"hidden\">";
            foreach($data as $key=>$field){
              if ($key != "all_text_timv" && $key != "object_profile_ssm"){
                if (is_array($field)){
                  foreach($field as $key=>$field_val){
                    if (is_array($field_val)){
                      $img_html .= implode("<br/>", $field_val)."<br/>";
                    } else {
                      $img_html .= $field_val . "<br/>";
                    }
                  }
                } else {
                  $img_html .= $field . "<br/>";
                }
              }
            }
           $img_html .= "</div>";
         }
         $img_html .= "</div>";
       } else {
         $img_html .= $errors['shortcodes']['fail'];
       }
      $i++;
   }
   if (isset($atts['speed']) && $atts['speed'] > 1){
     $interval = $atts['speed'];
   }
   if (isset($atts['auto']) && $atts['auto'] == 'on'){
     if (!isset($interval)){
      $interval = 5000;
     }
   } else if (isset($atts['auto']) && $atts['auto'] == 'off'){
     $interval = 'false';
   }
   $rand = rand();
   $gallery_html = '<div class="carousel slide '.$transition_class.'" id="carousel-'.$rand.'" data-height="'.$height.'" data-width="'.$width.'" data-interval="'.$interval.'"';
   if (isset($atts['max-height'])){
     $gallery_html .= " data-max-height='".$atts['max-height']."'";
   }
   if (isset($atts['max-width'])){
     $gallery_html .= " data-max-width='".$atts['max-width']."'";
   }
   $gallery_html .= '>';
   if (isset($atts['pager']) && $atts['pager'] == 'on'){
     $gallery_html .= '<ol class="carousel-indicators">';
     $i = 0;
     foreach($images as $id){
       $gallery_html .= '<li data-target="#carousel-'.$rand.'" data-slide-to="'.$i.'" class="';
       if ($i == 0){ $gallery_html .= "active";}
       $gallery_html .= '"></li>';
       $i++;
     }
     $gallery_html .= '</ol>';
   }
   $gallery_html .= '<div class="carousel-inner">'.$img_html.'</div>';
   if (isset($atts['nav']) && $atts['nav'] == 'on'){
     $gallery_html .= '<a class="left carousel-control" href="#carousel-'.$rand.'" role="button" data-slide="prev"><i class="glyphicon-chevron-left fa fa-chevron-left" aria-hidden="true"></i><span class="sr-only">Previous</span></a><a class="right carousel-control" href="#carousel-'.$rand.'" role="button" data-slide="next"><i class="glyphicon-chevron-right fa fa-chevron-right" aria-hidden="true"></i><span class="sr-only">Next</span></a>';
   }
   $gallery_html .= '</div>';
   $cache_output = $gallery_html;
   $cache_time = 1000;
   set_transient(md5('DRSTK'.serialize($atts)) , $cache_output, $cache_time * 60);
   return $gallery_html;
  }
}

function drstk_gallery_shortcode_scripts() {
	global $post, $wp_query;
	if( is_a( $post, 'WP_Post' ) && (has_shortcode( $post->post_content, 'drstk_gallery') || has_shortcode( $post->post_content, 'drstk_slider')) && !isset($wp_query->query_vars['drstk_template_type']) ) {
    wp_register_script( 'drstk_gallery',
        DRS_PLUGIN_URL . '/assets/js/gallery.js',
        array( 'jquery' ));
    wp_enqueue_script('drstk_gallery');
    wp_enqueue_script( 'ceres-all-js',
        DRS_PLUGIN_URL .  '/assets/js/ceres-and-plugins.js', array(
      'jquery',
      'masonry'
    ) );
    wp_register_style('drstk_item_gallery_style',  DRS_PLUGIN_URL . '/assets/css/plugins-all.min.css');
    wp_enqueue_style('drstk_item_gallery_style');
	}
}
add_action( 'wp_enqueue_scripts', 'drstk_gallery_shortcode_scripts');
