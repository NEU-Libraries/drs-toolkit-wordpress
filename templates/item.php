<?php
/**
 * The template for DRSTK items.
 */
get_header();

?>
<div id="content">
	<div class="ceres-row" id="title-container">
		<div class="<?php echo apply_filters( 'ceres_content_container_cls', 'container' ); ?> title-container">
			<div class="row">
				<div class="col-md-6">
					<h3><?php get_item_title(); ?></h3>
				</div>
				<div class="col-md-6">
					<ul class="breadcrumbs">
						<?php get_item_breadcrumbs(); ?>
					</ul>
				</div>
			</div>
		</div>
	</div>
<?php  $custom_content_placement = drstk_get_custom_content_data('placement'); ?>
	<div class="ceres-row site-content">
		<div class="<?php echo apply_filters( 'ceres_content_container_cls', 'container' ); ?>">
			<div class="row">
        <noscript>
        <div class="noscript_warning">PLEASE NOTE: JAVASCRIPT IS DISABLED ON YOUR BROWSER. For the best user experience, please enable javascript on your browser now.</div>
        </noscript>

				<div id="primary" class="content-area single col-md-12">
					<main id="main" class="site-main" role="main">
            <div id="drs-content" class="row">
              <div id="drs-loading"></div>
              <div id="drs-item-left" class="col-sm-6 one_half">
                <?php if ($custom_content_placement == 'top'): ?>
    							<div class="col-sm-12 drs-item-custom">
    								<?php echo drstk_get_custom_content_data('content');?>
    							</div>
                <?php endif; ?>

								<?php get_item_image(); ?>

	              <?php if ($custom_content_placement == 'middle'): ?>
    							<div class="col-sm-12 drs-item-custom">
    								<?php echo drstk_get_custom_content_data('content');?>
    							</div>
                <?php endif; ?>

								<?php get_related_content(); ?>
								<?php get_associated_files(); ?>
            	</div>
              <div id="drs-item-right" class="col-sm-6 last">
            		<div id="drs-item-details"><?php echo get_item_details($data); ?></div>
								<?php get_download_links(); ?>
            	</div>
              <?php if ($custom_content_placement == 'bottom'): ?>
  							<div class="col-sm-12 drs-item-custom">
  							  <?php echo drstk_get_custom_content_data('content');?>
  							</div>
              <?php endif; ?>

            </div><!-- #drs-content -->

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
<script type="text/javascript">add_google_tracking();</script>
