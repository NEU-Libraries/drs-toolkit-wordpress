<?php
/**
 * template for search/browse/collections/collection
 */
get_header();
wp_register_style('drstk_browse_style', DRS_PLUGIN_URL . "assets/css/plugins-all.min.css?ver=6.6.2");
wp_enqueue_style('drstk_browse_style');
wp_register_style('drstk_browse_style_2', DRS_PLUGIN_URL . "assets/css/style.css");
wp_enqueue_style('drstk_browse_style_2');
wp_register_script('drstk_browse_js', DRS_PLUGIN_URL . "assets/js/ceres-and-plugins.js?ver=4.9.26");
wp_enqueue_script('drstk_browse_js');
$view = 'search';
?>

<div id="content">
	<!--?php ceres_title_bar( $view ); ?-->

	<div class="ceres-row site-content">
		<div class="<?php echo apply_filters( 'ceres_content_container_cls', 'container' ); ?>">
			<div id="drs-loading"></div>
			<div id="drs-selection" class="row" style="display:none">
			  <div class="col-md-12">
				  <h2 class="results">Results filtered by: </h2>
			  </div>
		  </div>
			<div id="drs-browse-header-row" class="row">
				<div class="col-md-7 col-sm-12 col-xs-12" style="padding:0;">
					<div id="drs-per-page-div" class="col-md-6 col-sm-6 col-xs-6"></div>
					<div id="drs-sort" class="col-md-6 col-sm-6 col-xs-6" style="display:none"></div>
				</div>
				<div class="col-md-5 col-sm-12 col-xs-12" style="padding:0;">
					<div id="drs-pagination"><ul class="pag pagination"></ul></div>
				</div>
			</div><!-- #drs-browse-header-row -->
			<div id="drs-item-count"></div>
			<div class="row">
				<div id="primary" class="content-area col-md-9">
					<main id="main" class="site-main" role="main">
						<div id="drs-content" class="container">

							<div class="row">
								<div id="drs-facets" class="one_fourth col-md-4 hidden-phone hidden-xs hidden-sm"></div>
						  	<div id="drs-docs" class="three_fourth col-md-8 last">
							</div>
						  </div>
						</div><!-- #drs-content -->

					</main>
					<!-- #main -->
				</div>
				<!-- #primary -->

				<div id="secondary" class="widget-area main-sidebar col-md-3" role="complementary">

				</div><!-- #secondary -->

			</div>
			<!-- .row -->
		</div>
		<!-- .container -->
	</div>
	<!-- .ceres-row -->
</div><!-- #content -->
<?php get_footer(); ?>
