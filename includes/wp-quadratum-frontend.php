<?php

/**
 * WP_QuadratumFrontEnd - handles the front end display for the plugin
 */

class WP_QuadratumFrontEnd extends WP_PluginBase_v1_1 {
	private	$mxn;
	
	/**
	 * Class constructor
	 */
	
	function __construct () {
		$this->mxn = new WP_MXNHelper_v2_0;
		$this->mxn->register_callback ('cloudmade', array ($this, 'cloudmade_mxn_callback'));
		$this->mxn->register_callback ('nokia', array ($this, 'nokia_mxn_callback'));
		$this->mxn->register_callback ('googlev3', array ($this, 'googlev3_mxn_callback'));

		$provider = WP_Quadratum::get_option ('provider');
		$this->mxn->set_frontend_providers (array ($provider));
		
		add_shortcode ('wp_quadratum', array ($this, 'shortcode'));
	}
	
	/**
	 * WP MXN Helper callback; returns the configured CloudMade API key
	 */
	
	function cloudmade_mxn_callback () {
		$key = WP_Quadratum::get_option ('cloudmade_key');
		
		if (isset ($key)) {
			return (array ('key' => $key));
		}
	}
	
	/**
	 * WP MXN Helper callback; returns the configured Nokia API keys
	 */

	function nokia_mxn_callback () {
		$app_id = null;
		$auth_token = null;
	
		if (WP_Quadratum::is_wpna_installed () && WP_Quadratum::is_wpna_active ()) {
			$helper = new WPNokiaAuthHelper ();
		
			$tmp = $helper->get_id ();
			if (!empty ($tmp)) {
				$app_id = $tmp;
			}
		
			$tmp = $helper->get_token ();
			if (!empty ($tmp)) {
				$auth_token = $tmp;
			}
		}
	
		else {
			$app_id = WP_Quadratum::get_option ('nokia_app_id');
			$auth_token = WP_Quadratum::get_option ('nokia_app_token');
		}
		
		if (isset ($app_id) && isset ($auth_token)) {
			return array ('app-id' => $app_id, 'auth-token' => $auth_token);
		}
	}
	
	/**
	 * WP MXN Helper callback; returns the configured Google API key
	 */

	function googlev3_mxn_callback () {
		$key = WP_Quadratum::get_option ('google_key');
		$sensor = WP_Quadratum::get_option ('google_sensor');
		
		if (isset ($key) && isset ($sensor)) {
			return (array ('key' => $key, 'sensor' => $sensor));
		}
	}

	/**
	 * Create the HTML and Javascript for the checkin map
	 */

	static function render_checkin_map ($args) {
		// $args = array (
		//		'width' =>
		//		'height' =>
		//		'zoom' =>
		//		'private' =>
		//		'app-id' =>
		//		'app-token' =>
		//		'container-class' =>
		//		'container-id' =>
		//		'map-class' =>
		//		'map-id' =>
		//		'venue-class' =>
		//		'checkin' =>
		//	)

		$provider = WP_Quadratum::get_option ('provider');

		$checkin = $args['checkin'];
		$venue = $checkin->venue;
		$location = $venue->location;
		$categories = $venue->categories;
		$venue_url = 'https://foursquare.com/v/' . $venue->id;
		foreach ($categories as $category) {
			$icon_url = $category->icon;
			break;
		}
		if (is_object ($icon_url)) {
			$icon_url = $icon_url->prefix . '32' . $icon_url->name;
		}

		$content = array ();
		$tab = "\t";

		$js = array ();
		$js[] = '<script type="text/javascript">';
		$js[] = "var id = document.getElementById ('" . $args['map-id'] . "');";
		$js[] = "var map = new mxn.Mapstraction (id, '" . $provider . "');";
		$js[] = 'var coords = new mxn.LatLonPoint (' . $location->lat . ',' . $location->lng . ');';
		$js[] = 'map.setCenterAndZoom (coords, ' . $args['zoom'] . ');';
		$js[] = "var opts = {icon: '" . $icon_url . "', iconSize: [32, 32]};";
		$js[] = 'var marker = new mxn.Marker (coords);';
		$js[] = 'marker.addData (opts);';
		$js[] = 'map.addMarker (marker);';
		$js[] = '</script>';

		$content[] = '<div id="' . $args['container-id'] . '" class="' . $args['container-class'] .'" style="width:' . $args['width'] . 'px;">';
		$content[] = '<div id="' . $args['map-id'] . '" class="' . $args['map-class'] . '" style="width:' . $args['width'] . 'px; height:' . $args['height'] . 'px;"></div>';
		$content[] = '<div class="' . $args['venue-class'] . '">';
		$content[] = '<h5>Last seen at <a href="' . $venue_url . '" target="_blank">' . $venue->name . '</a> on ' . date ("d M Y G:i T", $checkin->createdAt) . '</h5>';
		$content[] = '</div>';
		$content[] = '</div>';

		return array_merge ($content, $js);
	}

	/**
	 * Shortcode handler for the [wp_quadratum] shortcode; expands the shortcode to the
	 * checkin map according to the current set of plugin settings/options.
	 *
	 * @param array atts Array containing the optional shortcode attributes specified by
	 * the current instance of the shortcode.
	 * @param string content String containing the enclosed content when the shortcode is
	 * specified in the enclosing form. If the self-closing form is used, this parameter will
	 * default to null.
	 * @return string String containing the checkin map, providing that the current set
	 * of settings/options permit this.
	 */

	function shortcode ($atts, $content=null) {
		// TODO: handle self-closing and enclosing shortcode forms properly
		// TODO: this function is fugly; need to break out the checkin acquisition
		// and map generation code into a function/functions that can be called by
		// both the shortcode, the widget and the the_content filter (when I write it)
		// TODO: check and handle error responses from the 4sq API
		// TODO: handle 4sq API response caching
		
		static $instance = 0;
		
		$container_id = 'wp-quadratum-shortcode-container-' . $instance;
		$map_id = 'wp-quadratum-shortcode-map-' . $instance;
		$content = array ();
		
		extract (shortcode_atts (array (
			'width' => 300,
			'height' => 300,
			'zoom' => 16,
			'private' => false
		), $atts));

		$json = WP_Quadratum::get_foursquare_checkins ();
		$checkins = $json->response->checkins->items;

		foreach ($checkins as $checkin) {
			$app_id = NULL;
			$app_token = NULL;
		
			if (WP_Quadratum::is_wpna_installed () && WP_Quadratum::is_wpna_active ()) {
				$helper = new WPNokiaAuthHelper ();
			
				$tmp = $helper->get_id ();
				if (!empty ($tmp)) {
					$app_id = $tmp;
				}
			
				$tmp = $helper->get_token ();
				if (!empty ($tmp)) {
					$app_token = $tmp;
				}
			}
		
			else {
				$app_id = WP_Quadratum::get_option ('app_id');
				$app_token = WP_Quadratum::get_option ('app_token');
			}
		
			$args = array ();
			$args['width'] = $width;
			$args['height'] = $height;
			$args['zoom'] = $zoom;
			$args['private'] = $private;
			$args['app-id'] = $app_id;
			$args['app-token'] = $app_token;
			$args['container-class'] = 'wp-quadratum-container';
			$args['container-id'] = 'wp-quadratum-container-' . $instance;
			$args['map-class'] = 'wp-quadratum-map';
			$args['map-id'] = 'wp-quadratum-map-' . $instance;
			$args['venue-class'] = 'wp-quadratum-venue';
			$args['checkin'] = $checkin;
			$content = WP_QuadratumFrontEnd::render_checkin_map ($args);
			$instance++;

			break;	// Not really needed as we only return a single checkin item
		}

		return implode (PHP_EOL, $content);
	}
	
}	// end class WP_QuadratumFrontEnd

$__wp_quadratumfrontend_instance = new WP_QuadratumFrontEnd;