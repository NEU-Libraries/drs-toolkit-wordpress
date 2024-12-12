<?php

/**
 * Tools here for connecting the broader WP/drs-tk environment to the 
 * semi-independent CERES code under /libraries.
 * 
 * Future of this approach is uncertain as of 2023-03-07 10:59:40
 * 
 * 
 * 
 */

use Ceres\ViewPackage\ViewPackage;

/* LOAD CERES */

require_once( plugin_dir_path( __FILE__ ) . '/libraries/Ceres/config/ceresSetup.php' );

/* REGISTER SCRIPTS AND STYLES USED BY PARTICULAR VIEW PACKAGES */

/* Registering them will make them available everywhere, but it looks
*  like I can enqueue them from within a shortcode handler, so I'll do 
*  it there based on need. 2023-03-07 17:58:38
*/

wp_register_script('ceres_js_setup' , plugins_url('/drs-tk/libraries/Ceres/config/ceresJsSetup.js'));
wp_enqueue_script('ceres_js_setup');
$ceresRootDir = CERES_ROOT_DIR;
wp_add_inline_script('ceres_js_setup',
	"var CERES_ROOT_URL = new URL(window.location.href).origin + '/Ceres';");



/* REGISTER GET PARAMS */
/* Used to pass data from one page to another, e.g. QIDs into inner queries */
// see https://developer.wordpress.org/reference/functions/get_query_var/
function ceres_query_vars( $qvars ) {
	$qvars[] = 'ceres_qid';
	return $qvars;
}
add_filter( 'query_vars', 'ceres_query_vars' );





/* Leaflet and Leaflet plugins */


//@todo make enqueuing conditional
wp_register_script('ceres_datatables' plugins_url('//cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js'));
wp_register_script('ceres_leaflet', plugins_url('/libraries/Ceres/assets/js/leaflet/leaflet1.7.1.js', __FILE__));
wp_register_script('ceres_leaflet_wicket', plugins_url('/libraries/Ceres/assets/js/leaflet/brc/wicket-1.3.8.js', __FILE__));
wp_register_script('ceres_leaflet_markercluster', plugins_url('/libraries/Ceres/assets/js/leaflet/leaflet-js-markercluster/leaflet.markercluster.js', __FILE__));
wp_register_script('ceres_leaflet_bostonboundaries', plugins_url('/libraries/Ceres/assets/js/leaflet/brc/bostonboundaries.js', __FILE__));
wp_register_script('ceres_leaflet_mask', plugins_url('/libraries/Ceres/assets/js/leaflet/leafet-plugin-mask.js', __FILE__));
wp_register_script('ceres_leaflet_boundary-canvas', plugins_url('/libraries/Ceres/assets/js/leaflet/brc/leaflet-boundary-canvas.js', __FILE__));
wp_register_script('ceres_leaflet_geolet', plugins_url('/libraries/Ceres/assets/js/leaflet/leaflet-plugin-geolet.js', __FILE__));
wp_register_script('ceres_leaflet_fuse', plugins_url('/libraries/Ceres/assets/js/leaflet/fuse-leaflet-plugin-6-6-2.js', __FILE__));
wp_register_style('ceres_leaflet', plugins_url('/libraries/Ceres/assets/css/leaflet/leaflet.css', __FILE__));
wp_register_style('ceres_leaflet_brc-project', plugins_url('/libraries/Ceres/assets/css/leaflet/leaflet-brc-project.css', __FILE__));
wp_register_style('ceres_leaflet_markercluster', plugins_url('/libraries/Ceres/assets/css/leaflet/leaflet-js-markercluster/MarkerCluster.css', __FILE__));
wp_register_style('ceres_leaflet_markercluster_default', plugins_url('/libraries/Ceres/assets/css/leaflet/leaflet-js-markercluster/MarkerCluster.Default.css', __FILE__));
wp_register_style('ceres_datatables', '//cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css');

//@todo  make enqueueing conditional upon the need
wp_enqueue_script('ceres_datatables');
wp_enqueue_script('ceres_leaflet');
wp_enqueue_script('ceres_leaflet_wicket');
wp_enqueue_script('ceres_leaflet_markercluster');
wp_enqueue_script('ceres_leaflet_bostonboundaries');
wp_enqueue_script('ceres_leaflet_mask');
wp_enqueue_script('ceres_leaflet_boundary-canvas');
wp_enqueue_script('ceres_leaflet_geolet');
wp_enqueue_script('ceres_leaflet_fuse');

wp_enqueue_style('ceres_leaflet');
wp_enqueue_style('ceres_leaflet_brc-project');
wp_enqueue_style('ceres_leaflet_markercluster');
wp_enqueue_style('ceres_leaflet_markercluster_default');
wp_enqueue_script('ceres_datatables');





/* SETUP SHORTCODES USED BY CERES */

add_shortcode('ceres_vp', 'ceres_vp_handler');
add_shortcode('ceres_renderer', 'ceres_renderer_handler');
add_shortcode('ceres_chinatown_qid', 'ceres_chinatown_qid_handler');
// add_shortcode('ceres_test', 'ceres_test_handler');

/* DEFINE THE HANDLERS USED BY THE SHORTCODES */

function ceres_chinatown_qid_handler($atts) {
	$atts = shortcode_atts(
		['vp_name'],
		$atts,
		'ceres_chinatown_qid_handler'
	);

	$qid = get_query_var('ceres_qid');


	$vp = new ViewPackage('chinatown_maintainers_list');
	$vp->setFetcherOptionValue(null, 'rqReplacements', ['maintainerQid', $qid]);



	return $qid;
}

function ceres_renderer_handler($atts) {
	$atts = shortcode_atts(
		array(
			'renderer' => '',
		),
		$atts,
		'ceres_renderer'		
	);
	$rendererName = $atts['renderer'];
	return "<h2>hi! I'll render from a $rendererName someday!</h2>";
}

function ceres_vp_handler($atts) {

    $atts = shortcode_atts(
		array(
			'vp_name' => '',
            'use_local_response_data' => false,
            'local_response_name' => '',	
			'extractor_reorder_mapping_name' => null,
			'extractor_remove_vars_name' => null,
			'extractor_value_label_mapping_name' => null,
		),
		$atts,
		'ceres_vp'
	);

	//doing this here to avoid any collisions from WP's attr filtering mechanism if I need to use it someday
	$atts = expandAttsToFilePath($atts);
	$vp = new ViewPackage($atts['vp_name']);

	$vp->build();

// @TODO generalize this

	switch ($atts['vp_name']) {
		case 'leaflet_wikidata_for_public_art_map':
		case 'leaflet_wikidata_for_public_art_table':
			$vp->setFetcherQueryFromFile(null, CERES_ROOT_DIR . '/data/rqFiles/publicart/leaflet.rq');
			$vp->getRenderer()->getFetcher()->setEndpoint('https://query.wikidata.org/sparql');	
		break;
	}

	//@todo make this sequence more coherent and general. somehow
	//make use of my fancy StrUtils to convert snakecase and camelcase?
	if (!is_null($atts['extractor_reorder_mapping_name'])) {
		$vp->setExtractorOptionValue(null, 'extractorReorderMappingFilePath', $atts['extractor_reorder_mapping_name']);
	}
	if (!is_null($atts['extractor_remove_vars_name'])) {
		$vp->setExtractorOptionValue(null, 'extractorRemoveVarsFilePath', $atts['extractor_remove_vars_name']);
	}
	if (!is_null($atts['extractor_value_label_mapping_name'])) {
		$vp->setExtractorOptionValue(null, 'extractorValueLabelMappingFilePath', $atts['extractor_value_label_mapping_name']);
	}



    $useLocalResponseData = $atts['use_local_response_data'];

    if($useLocalResponseData) {
        $vp->gatherData(null, $atts['local_response_name']);
    } else {
        $vp->gatherData();

    }
	return $vp->render();

}


/* HELPERS FOR THE SHORTCODE HANDLING */

//doing this here to avoid any collisions from WP's attr filtering mechanism if I need to use it someday
function expandAttsToFilePath(array $atts): array {

	foreach ($atts as $name=>$value) {
		switch ($name) {
			//attributes that go to `extractorData` directory
			case 'extra':
			case 'extractor_reorder_mapping_name':
			case 'extractor_remove_vars_name':
			case 'extractor_value_label_mapping_name':
				if (!is_null($value)) {
					$atts[$name] = CERES_ROOT_DIR . '/data/extractorData/' . $value . '.json';
				}
				
			break;
			//attributes to go to `staticQueryResponses` directory
			case 'local_response_name':
				$atts[$name] = CERES_ROOT_DIR . '/data/staticQueryResponses/' . $value . '.json';
			break;
			default:
		}
	}
	return $atts;
}
