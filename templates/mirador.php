<?php
/**
 * template for mirador viewer
 */

get_header();
$view = ceres_get_view();

?>

<div id="content">
	<?php ceres_title_bar($view); ?>

	<div class="ceres-row site-content">
		<div class="<?php echo apply_filters( 'ceres_content_container_cls', 'container' ); ?>">
			<div class="row">

				<div id="primary" class="content-area col-md-12">
					<main id="main" class="site-main" role="main">
						<?php
							if (get_option('drstk_mirador') != "") {
								?>
								<div id="mirador_viewer"></div>
							<?php
						} else {
							?>
							<p>Mirador hasn't been enabled. Please contact Toolkit staff to enable this feature.</p>
							<?php
						}
							 ?>

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
