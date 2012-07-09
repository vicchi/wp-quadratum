<?php

class WP_QuadratumFrontEnd extends WP_PluginBase {
	
	function __construct () {
		$this->hook ('wp_head', 'head', 1);
		$this->hook ('wp_enqueue_scripts', 'enqueue_scripts');
		
		add_shortcode ('wp_quadratum', array ($this, 'shortcode'));
	}
	
	function head () {
		echo '<meta http-equiv="X-UA-Compatible" content="IE=7; IE=EmulateIE9" />';
	}
	
	function enqueue_scripts () {
		wp_register_script ('nokiamaps', 'http://api.maps.nokia.com/2.1.1/jsl.js');
		wp_enqueue_script ('nokiamaps');
	}

	function render_checkin_map ($args) {
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
		if ((isset ($args['app-id']) && !empty ($args['app-id'])) && (isset ($args['app-token']) && !empty ($args['app-token']))) {
			$js[] = 'nokia.maps.util.ApplicationContext.set ({';
			$js[] = $tab . '"appId": "' . $args['app-id'] . '",';
			$js[] = $tab . '"authenticationToken": "' . $args['app-token'] . '"';
			$js[] = '});';
		}
		$js[] = 'var coords = new nokia.maps.geo.Coordinate (' . $location->lat . ',' . $location->lng . ');';
		$js[] = "var args = {'zoomLevel': " . $args['zoom'] . ", 'center': coords};";
		$js[] = "var marker = new nokia.maps.map.Marker (coords, {'icon': '" . $icon_url . "'});";
		$js[] = "var id = document.getElementById ('" . $args['map-id'] . "');";
		$js[] = 'var map = new nokia.maps.map.Display (id, args);';
		$js[] = 'map.objects.add (marker);';
		$js[] = '</script>';

		$content[] = '<div id="' . $args['container-id'] . '" class="' . $args['container-class'] .'" style="width:' . $args['width'] . 'px;">';
		$content[] = '<div id="' . $args['map-id'] . '" class="' . $args['map-class'] . '" style="width:' . $args['width'] . 'px; height:' . $args['height'] . 'px;"></div>';
//		$content[] = '<div id="' . $args['map-id'] . '" class="' . $args['map-class'] . '" style="width:' . $args['width'] . 'px; height:' . $args['height']'px;">';
		$content[] = '<div class="' . $args['venue-class'] . '">';
		$content[] = '<h5>Last seen at <a href="' . $venue_url . '" target="_blank">' . $venue->name . '</a> on ' . date ("d M Y G:i T", $checkin->createdAt) . '</h5>';
		$content[] = '</div>';
		$content[] = '</div>';

		return array_merge ($content, $js);
	}

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