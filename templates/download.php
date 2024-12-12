<?php
  if(is_user_logged_in()){
    $url = "https://repository.library.northeastern.edu/downloads/". $item_pid ."?datastream_id=content&token=" . drstk_drs_auth();
    wp_redirect($url);
    exit;
  } else {
    auth_redirect();
  }