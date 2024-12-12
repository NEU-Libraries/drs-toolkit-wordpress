<?php
add_action('add_meta_boxes_drstk_item_extension', 'adding_item_extension_meta_box');
function adding_item_extension_meta_box($post){
  add_meta_box(
    'drstk_meta_box',
    __('Item ID'),
    'render_item_extension_meta_box',
    'drstk_item_extension',
    'side',
    'high'
  );
  add_meta_box(
    'drstk_url_meta_box',
    __('Item URL Alias'),
    'render_item_url_meta_box',
    'drstk_item_extension',
    'side',
    'high'
  );
  
  add_meta_box (
    'drstk_placement_meta_box',
    'Placement for the custom content on the page',
    'render_drstk_placement_meta_box',
    'drstk_item_extension',
    'side',
    'high'
  );
  
}

function render_item_extension_meta_box(){
  global $post;
  wp_nonce_field(basename(__FILE__), "meta-box-nonce");
  $item_id = get_post_meta($post->ID, "item-id", true);
  ?>
  <div>
    <p>ie. neu:123 or dpla:890342</p>
    <input name="item-id" type="text" value="<?php echo $item_id; ?>">
    <p><a href="<?php echo get_site_url() . '/item/' . $item_id ?>"><?php echo $item_id; ?> </a></p>
  </div>
  <?php
}

function render_item_url_meta_box(){
  global $post;
  wp_nonce_field(basename(__FILE__), "meta-box-nonce");
  ?>
  <div>
    <p>Enter a custom URL alias for this item instead of its default, like item/neu:123. Do not include a leading slash. Examples could be "books/darwin" or "darwin"</p>
    <input name="item-url" type="text" value="<?php echo get_post_meta($post->ID, "item-url", true); ?>">
  </div>
  <?php
}

function render_drstk_placement_meta_box() {
  global $post;
  $currentlySelected = get_post_meta($post->ID, "drstk-custom-content-placement", true);
  
  wp_nonce_field(basename(__FILE__), "meta-box-nonce");
  $html =  "<div>";
  $html .= "<p>Select the placement for this custom content</p>";
  $html .= "<select name='drstk-custom-content-placement'>";
  if ($currentlySelected == 'top') {
    $html .= "  <option value='top' selected >Top</option>";
  } else {
    $html .= "  <option value='top' >Top</option>";
  }
  
  if ($currentlySelected == 'middle') {
    $html .= "  <option value='middle' selected >Middle</option>";
  } else {
    $html .= "  <option value='middle'  >Middle</option>";
  }
  
  if ($currentlySelected == 'bottom' || empty($currentlySelected)) {
    $html .= "  <option value='bottom' selected >Bottom</option>";
  } else {
    $html .= "  <option value='bottom' >Bottom</option>";
  }
  
  $html .= "</select>";
  $html .= "</div>";
  echo $html;
}

function save_custom_meta_box($post_id, $post, $update){
  $home_url = get_option('drstk_home_url');
  if (!isset($_POST["meta-box-nonce"]) || !wp_verify_nonce($_POST["meta-box-nonce"], basename(__FILE__))) {
    return $post_id;
  }

  if(!current_user_can("edit_post", $post_id)) {
      return $post_id;
  }

  if(defined("DOING_AUTOSAVE") && DOING_AUTOSAVE) {
    return $post_id;
  }

  $slug = "drstk_item_extension";
  if($slug != $post->post_type) {
    return $post_id;
  }

  $item_id = "";

  if(isset($_POST["item-id"])){
    $item_id = $_POST["item-id"];
  }
  update_post_meta($post_id, "item-id", $item_id);
  if(isset($_POST["item-url"])){
    $item_url = $_POST["item-url"];
  }
  update_post_meta($post_id, "item-url", $item_url);
  
  update_post_meta($post_id, "drstk-custom-content-placement", $_POST['drstk-custom-content-placement']);
}

add_action("save_post", "save_custom_meta_box", 10, 3);
