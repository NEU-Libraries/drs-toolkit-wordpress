<?php

add_action( 'wp_ajax_reload_filtered_set_timeline', 'reload_filtered_set_timeline_ajax_handler' ); //for auth users
add_action( 'wp_ajax_nopriv_reload_filtered_set_timeline', 'reload_filtered_set_timeline_ajax_handler' ); //for nonauth users
function reload_filtered_set_timeline_ajax_handler()
{
    if ($_POST['reloadWhat'] == "timelineReload") {
        echo drstk_timeline($_POST['atts'], $_POST['params']);
    }
    else if($_POST['reloadWhat'] == "facetReload") {
        if (isset($_POST['atts']['collection_id'])) {
          // @TODO
          $test = get_response("https://repository.library.northeastern.edu/api/v1/search/".$_POST['atts']['collection_id']);
          $test = json_decode($test);
          $url = drstk_api_url("drs", $_POST['atts']['collection_id'], "search", "date", "per_page=".count($test->pagination->table->total_count));
          if (isset($_POST['params']['f'])) {
              foreach ($_POST['params']['f'] as $facet => $facet_val) {
                  $url .= "&f[" . $facet . "][]=" . urlencode($facet_val);
              }
          }
          if (isset($_POST['params']['q']) && $_POST['params']['q'] != ''){
              $url .= "&q=". urlencode(sanitize_text_field($_POST['params']['q']));
          }
          $response = get_response($url);
          $facets_info_data = json_decode($response['output']);
          wp_send_json($facets_info_data);
        }
    }
    die();
}


add_action( 'wp_ajax_reloadRemainingTimeline', 'reloadRemainingTimeline_ajax_handler' ); //for auth users
add_action( 'wp_ajax_nopriv_reloadRemainingTimeline', 'reloadRemainingTimeline_ajax_handler' ); //for nonauth users
function reloadRemainingTimeline_ajax_handler()
{
    $a = get_post($_POST['post_id'])->post_content;
    $parsed_a = shortcode_parse_atts($a);
    echo drstk_timeline($parsed_a, $_POST['page_no']);
    die();
}

/* adds shortcode */

add_shortcode( 'drstk_timeline', 'drstk_timeline' );
function drstk_timeline( $atts, $params ){
  $errors = drstk_get_errors();

  $cache = get_transient(md5('PREFIX'.serialize($atts)));
  if($cache != NULL
      && ! WP_DEBUG
      && (!(isset($params))
      || $params == NULL)
      && !(isset($atts['collection_id']))
    ) {
         return $cache;
  }

    $color_codes = array();
    $color_hex = array();

    foreach($atts as $key => $value){
        if(preg_match('/(.*)_color_desc_id/',$key)){
            $color_codes[$key]=$value ;
        }
        if(preg_match('/(.*)_color_hex/',$key)){
            $color_hex[$key]=$value;
        }

    }
    if(!isset($atts['collection_id'])) {
        $neu_ids = array_map('trim', explode(',', $atts['id']));
    }

    $timeline_increments = $atts['increments'];
   // $color_codes = array("red", "green", "blue", "yellow", "orange");
    $current_color_code_id_values = array();
    $current_color_legend_desc_values = array();
    $index_color_pair = array();
    $facet_options = drstk_facets_get_option('drstk', true);
    
    foreach($color_codes as $color_code=>$color_code_values){
        //$current_color_code_id_string = $color_code . "_id";
        //$current_color_legend_desc_string = $color_code . "_desc";
        $current_color_code_id_value =$color_code_values;
        $current_color_legend_desc_value= substr($color_code,0,-14);
        $current_color_legend_desc_value = str_replace('_',' ',$current_color_legend_desc_value);
        $current_color_legend_desc_value = ucwords($current_color_legend_desc_value);

        if(!is_null($current_color_code_id_value)){
            $current_color_code_ids = explode(",", $current_color_code_id_value);
            foreach($current_color_code_ids as $current_color_code_id){
                $current_color_code_id_values[str_replace(' ', '', $current_color_code_id)] = $color_code;
            }
        }
        if(!is_null($current_color_legend_desc_value)){$current_color_legend_desc_values[$color_code] = $current_color_legend_desc_value;}
    }

    $event_list = array();
    $timeline_html = "";
    $counter = 1;

    /*
    If collection_id attribute is set, then load the DRS items directly using the search API.
  */

    $collectionItemsId = array();

    $facets_info_data = array();



    $collectionCheck =null;
    if(isset($atts['collection_id'])){
      // @TODO
      $test = get_response("https://repository.library.northeastern.edu/api/v1/search/".$atts['collection_id']);
      $test = json_decode($test);

        $url = drstk_api_url("drs", $atts['collection_id'], "search", "date", "per_page=".$test->pagination->table->total_count);

        if (isset($params['f'])) {
            foreach ($params['f'] as $facet => $facet_val) {
                $url .= "&f[" . $facet . "][]=" . urlencode($facet_val);
            }
        }

        if (isset($params['q']) && $params['q'] != ''){
            $url .= "&q=". urlencode(sanitize_text_field($params['q']));
        }

        if(isset($params['page_no'])){
            $url .= "&page=" . $params['page_no'];
         }

        $response = get_response($url);
        $facets_info_data = json_decode($response['output']);
        $num_pages = $data1->pagination->table->num_pages;

        if($num_pages == 0){
            return "No Result";
        }

        if(isset($params['page_no']) && $params['page_no'] > $num_pages){
            return "All_Pages_Loaded";
        }

        $docs2 = $data1->response->response->docs;

        foreach($docs2 as $docItem){
            $collectionItemsId [] = $docItem->id;
        }

        $neu_ids = $collectionItemsId;
    }


    foreach($neu_ids as $current_key => $neu_id){

        $repo = drstk_get_repo_from_pid($neu_id);
        if ($repo != "drs"){$pid = explode(":",$neu_id); $pid = $pid[1];} else {$pid = $neu_id;}
        if($repo == "drs"){
            $url = drstk_api_url("drs", $neu_id, "files", NULL, "solr_only=true");
            $response = get_response($url);
            $data = json_decode($response['output']);

            if (!isset($data->error)){
                $data = $data->_source;
                $pid = $data->id;
                if (!isset($data->key_date_ssi)){
                    continue;
                }
                $key_date = $data->key_date_ssi;
                $current_array = array();
                $thumbnail_url = "https://repository.library.northeastern.edu".$data->fields_thumbnail_list_tesim[2];

                if (isset($atts['metadata'])){
                  $timeline_metadata = '';
                  $metadata = explode(",",$atts['metadata']);
                  foreach($metadata as $field){
                     if (isset($data->$field)){
                       $this_field = $data->$field;
                      if (isset($this_field)){
                        if (is_array($this_field)){
                          foreach($this_field as $val){
                            if (is_array($val)){
                              $timeline_metadata .= implode("<br/>",$val) . "<br/>";
                            } else {
                              $timeline_metadata .= $val ."<br/>";
                            }
                          }
                        } else {
                          $timeline_metadata .= $this_field . "<br/>";
                        }
                      }
                    }
                  }
                  $text = htmlentities($timeline_metadata);
                } else {
                  $text = "<p>&nbsp;</p>";
                }

                if ($text == NULL || $text == ""){
                    $text = "<p>&nbsp;</p>";
                }
                $caption = "";
                $headline = htmlentities($data->full_title_ssi);

                $keys = (array)$key_date;
                $key_date_explode = explode("/",$keys[0]);


                $timeline_html .= "<div class=\"timelineclass\" data-url=\"".$thumbnail_url."\" data-caption=\"".$caption."\" data-credit=\" \" data-year=\"".$key_date_explode[0]."\" data-month=\"".$key_date_explode[1]."\" data-day=\"".$key_date_explode[2]."\" data-headline=\"".$headline."\" data-text=\"".$text."\" data-pid=\"".$pid."\" data-full=\"";
                $timeline_html .= drstk_home_url()."item/".$pid."\">";
                $timeline_html .= "</div>";
            }else {
                $timeline_html = $errors['shortcodes']['fail'];
            }
            if (isset($current_color_code_id_values[str_replace(' ', '', $neu_id)])) {
                $present_id_color = $current_color_code_id_values[str_replace(' ', '', $neu_id)];
            } else {
                $present_id_color = NULL;
            }
            $index_color_pair[str_replace(":","",$pid)] = $present_id_color;
        }
        if ($repo == "wp"){
            if (!isset($timeline_custom_html)){$timeline_custom_html = "";}
            $post = get_post($pid);
            $url = $post->guid;
            if (strpos($post->post_mime_type, "audio") !== false || strpos($post->post_mime_type, "video") !== false){
                $url = drstk_home_url()."/wp-includes/images/media/video.png";
            }
            $title = $post->post_title;
            $description = $post->post_excerpt;
            $custom = get_post_custom($pid);
            if (!isset($custom['_timeline_date'])){
                continue;
            }
            $date = $custom['_timeline_date'][0];
            if ($date != ""){
                $date = explode("/", $date);
                $year = $date[0];
                $month = $date[1];
                $day = $date[2];
                if (isset($current_color_code_id_values["wp:".$pid])){
                    $colorGroup = $current_color_code_id_values["wp:".$pid];
                    $index_color_pair["wp".$pid] = $colorGroup;
                }
                $timeline_custom_html .= "<div class='timelineclass' data-credit='' data-url=".$url." data-year='".$year."' data-month='".$month."' data-day='".$day."' data-caption='' data-headline='".htmlspecialchars($title, ENT_QUOTES, 'UTF-8')."' data-text='".htmlspecialchars($description, ENT_QUOTES, 'UTF-8')."' data-pid='wp".$post->ID."' data-full='".drstk_home_url()."item/wp:".$post->ID."'";
                $timeline_custom_html .= "></div>";
            } else {
                //no date
                continue;
            }
        }
        if ($repo == "dpla"){
            if (!isset($timeline_custom_html)){$timeline_custom_html = "";}
            $url = drstk_api_url("dpla", $pid, "items");
            $response = get_response($url);
            $data = json_decode($response['output']);
            if (isset($data->docs[0]->object)){
                $url = $data->docs[0]->object;
            } else {
              $url = DPLA_FALLBACK_IMAGE_URL;
            }
            $title = $data->docs[0]->sourceResource->title;
            if (is_array($title)){
                $title = implode("<br/>",$title);
            }
            if (isset($data->docs[0]->sourceResource->description)){
                $description = $data->docs[0]->sourceResource->description[0];
            } else {
                $description = "";
            }
            $text = $description;
            $data->abstract_tesim = $description;
            if (isset($data->docs[0]->sourceResource->creator)){
                $data->creator_tesim = $data->docs[0]->sourceResource->creator;
            }
            if (isset($atts['metadata'])){
              $timeline_metadata = '';
              $metadata = explode(",",$atts['metadata']);
              foreach($metadata as $field){
                 if (isset($data->$field)){
                   $this_field = $data->$field;
                  if (isset($this_field)){
                    if (is_array($this_field)){
                      foreach($this_field as $val){
                        if (is_array($val)){
                          $timeline_metadata .= implode("<br/>",$val) . "<br/>";
                        } else {
                          $timeline_metadata .= $val ."<br/>";
                        }
                      }
                    } else {
                      $timeline_metadata .= $this_field . "<br/>";
                    }
                  }
                }
              }
              $text = htmlentities($timeline_metadata);
            } else {
              $text = "<p>&nbsp;</p>";
            }
            if (isset($data->docs[0]->sourceResource->rights)){
                if (is_array($data->docs[0]->sourceResource->rights)){
                    $credit = implode("<br/>",$data->docs[0]->sourceResource->rights);
                } else {
                    $credit = $data->docs[0]->sourceResource->rights;
                }
            } else {
                $credit = "";
            }
            if ((isset($data->docs[0]->sourceResource->date->displayDate) && $data->docs[0]->sourceResource->date->displayDate != "Unknown") || (isset($data->docs[0]->sourceResource->date[0]->displayDate) && $data->docs[0]->sourceResource->date[0]->displayDate != "Unknown")){
                if (is_array($data->docs[0]->sourceResource->date)){
                    $date = $data->docs[0]->sourceResource->date[0]->displayDate;
                } else {
                    $date = $data->docs[0]->sourceResource->date->displayDate;
                }
                $date = explode("-", $date);
                $year = $date[0];
                if (strlen($year) != 4){
                    $year = $data->docs[0]->sourceResource->date->begin;
                    if (strlen($year) != 4){
                      $date = explode("-", $year);
                        $year = $date[0];
                    }
                }
                if (isset($date[1])){
                  $month = $date[1];
                } else {
                  $month = 1;
                }
                if (isset($date[2])){
                  $day = $date[2];
                } else {
                  $day = 1;
                }
                if (isset($current_color_code_id_values["dpla:".$pid])){
                    $colorGroup = $current_color_code_id_values["dpla:".$pid];
                    $index_color_pair["dpla".$pid] = $colorGroup;
                } else {
                    $colorGroup = "";
                }
                $timeline_custom_html .= "<div class='timelineclass' data-credit='".$credit."' data-url=".$url." data-year='".$year."' data-month='".$month."' data-day='".$day."' data-caption=' ' data-headline='".htmlspecialchars($title, ENT_QUOTES, 'UTF-8')."' data-text='".$text."' data-pid='dpla".$pid."' data-full='".drstk_home_url()."item/dpla:".$pid."' data-colorGroup=".$colorGroup."";
                $timeline_custom_html .= "></div>";
            } else {
                //no date
                continue;
            }
        }
    }
    $color_ids_html_data = '';
    $color_desc_html_data = '';
    $sample_id_html_data = '';
    forEach($current_color_legend_desc_values as $key => $value){
        $color_hex_code='#';
        foreach ($color_hex as $hex_key => $hex_value){
            $hex_key = substr($hex_key,0,-10);
            $desc_key = substr($key,0,-14);;
            if($hex_key==$desc_key){
                $color_hex_code.= $hex_value;
            }
        }
        $color_desc_html_data .= "<tr><td width=\"1%\" bgcolor=\"". $color_hex_code ."\"></td><td>" . $value ."</td></tr>";
    }

    forEach($index_color_pair as $key_index => $color_value){
        $color_hex_code='#';
        foreach ($color_hex as $hex_key => $hex_value){
            $hex_key = substr($hex_key,0,-10);
            $desc_key = substr($color_value,0,-14);;
            if($hex_key==$desc_key){
                $color_hex_code.= $hex_value;
            }
        }
        $color_ids_html_data .= " data-" . str_replace('/', '', $key_index) . "='" . $color_hex_code . "' ";
    }

    if (isset($atts['custom_timeline_urls']) && ($atts['custom_timeline_urls'] != '')) {
        if (!isset($timeline_custom_html)){$timeline_custom_html = "";}
        $custom_timeline_urls = explode(",",$atts['custom_timeline_urls']);
        $custom_timeline_titles = explode(",",$atts['custom_timeline_titles']);
        $custom_timeline_descriptions = explode(",",$atts['custom_timeline_descriptions']);
        $custom_timeline_date = explode(",",$atts['custom_timeline_date']);
        $custom_timeline_color_groups = explode(",",$atts['custom_timeline_color_groups']);

        foreach($custom_timeline_urls as $key=>$value) {
            $url = $value;
            $title = $custom_timeline_titles[$key];
            $title = trim($title,'\'');
            $description = $custom_timeline_descriptions[$key];
            $description = trim($description,'\'');
            $date = explode('/',$custom_timeline_date[$key]);
            $year = trim($date[0], '\'');
            $month = $date[1];
            $day = trim($date[2], '\'');
            $colorGroup = $custom_timeline_color_groups[$key];

            $timeline_custom_html .= "<div class='custom-timeline' data-url=".$url." data-year='".$year."' data-month='".$month."' data-day='".$day."' data-title='".htmlspecialchars($title, ENT_QUOTES, 'UTF-8')."' data-description='".htmlspecialchars($description, ENT_QUOTES, 'UTF-8')."' data-colorGroup=".$colorGroup."";
            $timeline_custom_html .= "></div>";
        }
    }

    $shortcode = "<div id='timeline-embed' style=\"width: 100%; height: 500px\"></div>";
    $shortcode .= "<div id='timeline-table'><table id='timeline-table-id' style=\" float: right; width: 200px;\">". $color_desc_html_data ."</table></div>";
    $shortcode .= "<div id='timeline'>".$timeline_html."</div>";
    $shortcode .= "<div id='timeline-increments' data-increments='".$timeline_increments."'></div>";
    $shortcode .= "<div id=\"drs-facets\" class=\"one_fourth col-md-3 hidden-phone hidden-xs hidden-sm\"></div>";


    if (isset($timeline_custom_html)){
        $shortcode .= "<div id='timeline-custom-data'>".$timeline_custom_html."</div>";
    }

    if($color_ids_html_data != '' || $color_desc_html_data != ''){
        $shortcode .= "<div id='timeline-color-ids'" . $color_ids_html_data . "></div>";
    }
    $cache_output = $shortcode;
    $cache_time = 1000;
    set_transient(md5('PREFIX'.serialize($atts)) , $cache_output, $cache_time * 60);

    if(isset($atts['collection_id'])) {
        wp_register_script('drstk_timelineCollection', DRS_PLUGIN_URL . '/assets/js/timelineCollection.js', array('jquery'));
        wp_enqueue_script('drstk_timelineCollection');

        $reload_filtered_set_drs_nonce = wp_create_nonce('reload_filtered_set_drs');

        $timeline_nonce = wp_create_nonce('timeline_nonce');

        $timeline_obj = array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => $timeline_nonce,
            'home_url' => drstk_home_url()
        );

        $facets_info_data_obj = array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => $reload_filtered_set_drs_nonce,
            "data" => $facets_info_data,
            'home_url' => drstk_home_url(),
            "atts" => $atts,
            "timeline_obj" => $timeline_obj
        );

        wp_localize_script('drstk_timelineCollection', 'facets_info_data_obj', $facets_info_data_obj);
    }

    return $shortcode;
}



function drstk_timeline_shortcode_scripts() {
    global $post, $wp_query;

    if( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'drstk_timeline') && !isset($wp_query->query_vars['drstk_template_type']) ) {
        wp_register_script( 'drstk_timelinejs',
            'https://cdn.knightlab.com/libs/timeline3/latest/js/timeline.js',
            array( 'jquery' ));
        wp_enqueue_script('drstk_timelinejs');
        wp_register_style( 'drstk_cdn_timeline_css', 'https://cdn.knightlab.com/libs/timeline3/latest/css/timeline.css');
        wp_enqueue_style( 'drstk_cdn_timeline_css');
        wp_register_script( 'drstk_timelinepage',
            DRS_PLUGIN_URL . '/assets/js/timelinepage.js',
            array( 'jquery' ));
        wp_enqueue_script('drstk_timelinepage');

        $collectionSet = "";

        if(isset($temp['collection_id']) && $temp['collection_id'] != ''){
            $collectionSet = "checked";
        }

        $timeline_nonce = wp_create_nonce( 'timeline_nonce' );
        $timeline_obj = array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => $timeline_nonce,
            'home_url' => drstk_home_url(),
            'post_id' => $post->ID,
            'collectionSet' => $collectionSet
        );

        wp_localize_script( 'drstk_timelinepage', 'timeline_obj', $timeline_obj );


    }

}
add_action( 'wp_enqueue_scripts', 'drstk_timeline_shortcode_scripts');
