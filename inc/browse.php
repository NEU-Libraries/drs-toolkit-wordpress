<?php
add_action( 'wp_ajax_get_browse', 'browse_ajax_handler' ); //for auth users
add_action( 'wp_ajax_nopriv_get_browse', 'browse_ajax_handler' ); //for nonauth users
function browse_ajax_handler() {
  // Handle the ajax request
  $errors = drstk_get_errors();
  check_ajax_referer( 'browse_drs' );
  $collection = drstk_get_pid();

  if ($collection == '' || $collection == NULL) {
      $data = array('error'=>$errors['search']['missing_collection']);
      $data = json_encode($data);
      wp_send_json($data);
      wp_die();
  } elseif ($collection == "https://repository.library.northeastern.edu/collections/neu:1") {
    $data = array('error'=>$errors['search']['missing_collection']);
    $data = json_encode($data);
    wp_send_json($data);
    wp_die();
  } else {
    if (isset($_POST['params']['collection'])){
       $url = drstk_api_url("drs", $_POST['params']['collection'], "search");
    } else {
      $url = drstk_api_url("drs", $collection, "search");
    }
    if (isset($_POST['params']['q'])){
      $url .= "q=". urlencode(sanitize_text_field($_POST['params']['q']));
    }
    if (isset($_GET['q'])){
      $url .= "q=". urlencode(sanitize_text_field($_GET['q']));
    }
    if (isset($_POST['params']['per_page'])) {
      $url .= "&per_page=" . $_POST['params']['per_page'];
    }
    if (isset($_POST['params']['page'])) {
      $url .= "&page=" . $_POST['params']['page'];
    }
    if (isset($_POST['params']['f'])) {
      foreach($_POST['params']['f'] as $facet=>$facet_val){
        $url .= "&f[" . $facet . "][]=" . urlencode($facet_val);
      }

    }
    if (isset($_POST['params']['sort'])) {
      $url .= "&sort=" . $_POST['params']['sort'];
    }
    $response = get_response($url);
    wp_send_json($response['output']);
    wp_die();
  }
}

add_action('wp_ajax_wp_search', 'ajax_wp_search');
add_action('wp_ajax_nopriv_wp_search', 'ajax_wp_search');

function ajax_wp_search(){
  global $wp_query, $paged, $post;
  $related_content_title = get_option('drstk_search_related_content_title');
  $query_string = isset($_GET['query']) ? $_GET['query'] : "";
  $paged = $_GET['page'];
  if (isset($_GET['query']) && $query_string != ''){
    $query_args = array( 's' => $query_string, 'post_type'=>array('post', 'page'), 'posts_per_page'=>3, 'paged'=>$paged, 'post_status'=>'publish');
    $wp_query = new WP_Query( $query_args );
    $rel_query = relevanssi_do_query($wp_query);
    if (count($rel_query) > 0){
      foreach($rel_query as $r_post){
        $post = $r_post;
        $the_post = $post;
        get_template_part( 'content', 'excerpt' );
      }
      echo the_posts_pagination( array( 'mid_size'  => 2 ) );
    } else {
      echo "No ".strtolower($related_content_title)." was found";
    }
  } else {
    echo "Please enter a search term to retrieve ".strtolower($related_content_title);
  }
  wp_reset_postdata();
  die();
}
