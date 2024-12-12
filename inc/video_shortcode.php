<?php
/* adds shortcode */
add_shortcode( 'drstk_collection_playlist', 'drstk_collection_playlist' );
add_shortcode( 'drstk_media', 'drstk_collection_playlist' );
function drstk_collection_playlist($atts){
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
    $collection = array_map('trim', explode(',', $atts['id']));
    $playlists = '';
    if (isset($atts['height']) && $atts['height'] != 0){
      $height = $atts['height'];
    } else {
      $height = '270';
    }
    if (isset($atts['width']) && $atts['width'] != 0){
      $width = $atts['width'];
    } else {
      $width = '100%';
    }
    if (isset($atts['aspectratio'])){
      $aspectratio = $atts['aspectratio'];
    } else {
      $aspectratio = '16:9';
    }
    if (isset($atts['skin'])){
      $skin = $atts['skin'];
    }
    if (isset($atts['listbarwidth']) && $atts['listbarwidth'] != 0){
      $listbarwidth = $atts['listbarwidth'];
    } else {
      $listbarwidth = '250';
    }
    foreach($collection as $video){
      $repo = drstk_get_repo_from_pid($video);
      if ($repo != "drs"){$pid = explode(":",$video); $pid = $pid[1];} else {$pid = $video;}
      $poster;
      if ($repo == "drs"){
        $url = drstk_api_url("drs", $video, "files", null, "solr_only=true");
        $response = get_response($url);
        $data = json_decode($response['output']);
        $data = $data->_source;
        $objects_url = "https://repository.library.northeastern.edu/api/v1/files/" . $video . "/content_objects";
        $response = get_response($objects_url);
        $objects_data = json_decode($response['output']);
        $data = (object) array_merge((array) $data, (array) $objects_data);
        if (!isset($data->error)){

          $poster[] = "https://repository.library.northeastern.edu".$data->fields_thumbnail_list_tesim[4];
          $this_poster = "https://repository.library.northeastern.edu".$data->fields_thumbnail_list_tesim[4];
          $title = $data->title_info_title_ssi;
          $title = str_replace('"','\"', $title);
          foreach($data->canonical_object as $key=>$val){
            $pid = $key;
            $pid = explode("/", $pid);
            $pid = end($pid);
            $pid = str_replace("?datastream_id=content","",$pid);
            if ($val == 'Audio File'){
              $type = 'MP3';
              $provider = 'sound';
            }
            if ($val == 'Video File'){
              $type = 'MP4';
              $provider = 'video';
            }
            $playlists .= '{ sources: [ { file: "https://repository.library.northeastern.edu/wowza/' . $pid . '/plain", type: "'.strtolower($type).'" },';
            $playlists .= '{ file: "https://repository.library.northeastern.edu/wowza' .  $pid . '/playlist.m3u8"},';
            $playlists .= ' ], image: "' . $this_poster . '", title: "' . $title . '" },';
          }
        } else {
          return $errors['shortcodes']['fail'];
        }

      }
      if ($repo == "wp"){
        $post = get_post($pid);
        $this_poster = "";
        $poster[0] = "";
        $title = $post->post_title;
        $title = str_replace('"','\"', $title);
        if (strpos($post->post_mime_type, "video") !== false){
          $provider = 'sound';
        }
        if (strpos($post->post_mime_type, "audio") !== false){
          $provider = 'audio';
        }
        $playlists .= '{sources:[{file:"'.$post->guid.'",title:"'.$title.'"}],title:"'.$title.'"}';
        $playlist = $post->guid;
      }

      $download = 'download';

    }
    $pid_selector = "drs-item-video-".str_replace(':', "-", $pid);
    $cache_output = '<div id="'.$pid_selector.'">
        <img style="width: 100%;" src="' . $poster[0] .'" />
      </div>
      <script type="text/javascript">
        jwplayer.key="' . JWPLAYER_KEY . '";
        var primary = "html5";
        var provider = "'.$provider.'";
        var is_chrome = navigator.userAgent.indexOf(\'Chrome\') > -1;
        var is_safari = navigator.userAgent.indexOf("Safari") > -1;
        if ((is_chrome)&&(is_safari)) {is_safari=false;}
        jQuery(document).ready(function($){
        jwplayer("'.$pid_selector.'").setup({
          width: "'.$width.'",
          height: "'.$height.'",
          rtmp: { bufferlength: 5 } ,
          image: "'.$this_poster.'",
          provider: "'.$provider.'",
          androidhls: "true",
          hlshtml: "true",
          aspectratio:"'.$aspectratio.'",';
          if (isset($skin)){
            $cache_output .= 'skin: "'.$skin.'",';
          }
          $cache_output .='primary: primary,';
    if(count($collection) > 1){
        $cache_output .= 'listbar: {
          position: "right",
          size: 250,
          layout: "basic"
        },';
    }
    
      // $playlist is only set to something for wp content,
      // so cheesily cover up the NOTICE when it isn't set in the jwplayer setup for safari
      // @TODO I'd (PMJ would prefer a better way to build the JS
      // to branch around that, but whatevs for now
      if(empty($playlist)) {
        $playlist = '';
      }
      $cache_output .= 'playlist: [ '. $playlists . ']
        });
        jwplayer("'.$pid_selector.'").on("ready", function() {
         if (is_safari){
           //defaulting to m3u8 stream for safari since it functions better
           jwplayer("'.$pid_selector.'").load([{image: "'.$this_poster.'", sources:[{ file: "'.$playlist.'"}]}]);
           // Set poster image for video element to avoid black background for audio-only programs.
           $("'.$pid_selector.' video").attr("poster", "'.$this_poster.'");
         }
        });
        function errorMessage() {
          $("#drs-item-video").before("<div class=\'alert alert-warning\'>'.$errors['item']['jwplayer_fail'].'<br /><strong>Error Message:</strong> "+e.message+"</div>");
        }
        jwplayer("'.$pid_selector.'").on(\'error\', function(){
          errorMessage();
        });
        jwplayer("'.$pid_selector.'").on(\'setupError\', function(){
          errorMessage();
        });
        jwplayer("'.$pid_selector.'").on(\'buffer\', function() {
          theTimeout = setTimeout(function(e) {
            errorMessage(e);
          }, 5000);
        });
        jwplayer("'.$pid_selector.'").on("play", function(){
           clearTimeout(theTimeout);
         });
      });
      </script>';
    $cache_time = 1000;
    set_transient(md5('DRSTK'.serialize($atts)) , $cache_output, $cache_time * 60);
    return $cache_output;

}

function drstk_video_shortcode_scripts() {
    global $post, $wp_query;
    if( is_a( $post, 'WP_Post' ) && (has_shortcode( $post->post_content, 'drstk_collection_playlist') || has_shortcode( $post->post_content, 'drstk_media')) && !isset($wp_query->query_vars['drstk_template_type']) ) {
      wp_register_script('drstk_cdn_jwplayer', 'https://content.jwplatform.com/libraries/dTFl0VEe.js');
      wp_enqueue_script('drstk_cdn_jwplayer');
    }
}
add_action( 'wp_enqueue_scripts', 'drstk_video_shortcode_scripts');
