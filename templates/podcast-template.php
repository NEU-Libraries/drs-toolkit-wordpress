<?php 

wp_register_script('drstk_cdn_jwplayer', 'https://content.jwplatform.com/libraries/dTFl0VEe.js');
wp_enqueue_script('drstk_cdn_jwplayer');

wp_register_style('drstk_podcasts_style', DRS_PLUGIN_URL . "assets/css/podcasts.css");
wp_enqueue_style('drstk_podcasts_style');

get_header();

$page_id = get_option('drstk_podcast_page');
$page_object = get_page( $page_id );

$queryOptions = array(
  'action' => 'search',
  'sub_action' => 'av',
);

$queryParams = array(
  'sort' => 'date_ssi+desc',
  'per_page' => get_option('drstk_default_browse_per_page'),
);

//the default collection/set for the podcasts, from CERES Settings page
$resourceId = drstk_get_pid();

$fetcher = new Ceres_Drs_Fetcher($queryOptions, $queryParams);
$renderer = new Ceres_Podcast_Renderer($fetcher, $resourceId);
$jwPlayerOptions = array();
if (get_option('drstk_podcast_poster') == 'on') {
  $jwPlayerOptions['audioPoster'] = true;
}
$renderer->setJwPlayerOptions($jwPlayerOptions);
?>


<div id="content">
	<div class="ceres-row site-content">
		<div class="<?php echo apply_filters( 'ceres_content_container_cls', 'container' ); ?>">
			<div id="drs-loading"></div>
			<div class="row">
				<div id="primary" class="content-area col-md-9">
				
					<main id="main" class="site-main" role="main">
					
						<h2><?php echo apply_filters('the_title', $page_object->post_title); ?></h2>
							<ul class='republisher-links'>
							  <?php if($itunesLink = get_option('drstk_itunes_link') ): ?>
						  	<li class='republisher-link' id='itunes-link'>
						  		<a href='<?php echo $itunesLink; ?>'>
						  			<img src='<?php echo DRS_PLUGIN_URL . "assets/images/US_UK_Apple_Podcasts_Listen_Badge_RGB.svg"; ?>' 
						  			   alt='Subscribe in iTunes' />
						  		</a>
						  	</li>
						  	<?php  endif; ?>
						  	
						  	<?php if($stitcherLink = get_option('drstk_stitcher_link') ): ?>
						  	<li class='republisher-link' id='stitcher-link'>
						  		<a href='<?php echo $stitcherLink; ?>'>
						  			<img src='<?php echo DRS_PLUGIN_URL . "assets/images/Stitcher-button-300x110.jpg"; ?>' 
						  			   alt='Subscribe in Stitcher' />
						  		</a>
						  	</li>
						  	<?php  endif; ?>
						  	
						  	<?php if($overcastLink = get_option('drstk_overcast_link') ): ?>
						  	<li class='republisher-link' id='overcast-link'>
						  		<a href='<?php echo $overcastLink; ?>'>
						  			<img src='<?php echo DRS_PLUGIN_URL . "assets/images/Overcastbutton2-300x106.png"; ?>' 
						  			   alt='Subscribe in Overcast' />
						  		</a>
						  	</li>
						  	<?php  endif; ?>
						  	
						  	<?php if($googleplayLink = get_option('drstk_googleplay_link') ): ?>
						  	<li class='republisher-link' id='googleplay-link'>
						  		<a href='<?php echo $googleplayLink; ?>'>
						  			<img src='<?php echo DRS_PLUGIN_URL . "assets/images/PodcastAvailableonGooglePlayMusic-300x110.png"; ?>' 
						  			   alt='Subscribe in Google Play' />
						  		</a>
						  	</li>
						  	<?php  endif; ?>
						  	
						  	<?php if($spotifyLink = get_option('drstk_spotify_link') ): ?>
						  	<li class='republisher-link' id='spotify-link'>
						  		<a href='<?php echo $spotifyLink; ?>'>
						  			<img src='<?php echo DRS_PLUGIN_URL . "assets/images/Spotify-images-300x110.png"; ?>' 
						  			   alt='Subscribe in Spotify' />
						  		</a>
						  	</li>
						  	<?php  endif; ?>  							  							  	
						  </ul>
						
						<div id="drs-content" class="container">
						  <div class="row">
						    <?php echo apply_filters('the_content', $page_object->post_content);?>
						  </div>
						  <?php echo $renderer->render(); ?>
						</div>

					</main>
					<!-- #main -->
				</div>
				<!-- #primary -->
			</div>
			<!-- .row -->
		</div>
		<!-- .container -->
	</div>
	<!-- .ceres-row -->
</div><!-- #content -->

<?php get_footer(); ?>
