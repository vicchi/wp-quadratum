<?php

/**
 * WP_QuadratumFrontEnd - handles the front end display for the plugin
 */

class WP_QuadratumFrontEnd extends WP_PluginBase_v1_1 {
	//private	$mxn;

	const ID_PREFIX = 'wp_quadratumwidget';
	const INACTIVE_WIDGETS = 'wp_inactive_widgets';
	const WIDGET_VERSION = 'array_version';

	private static $instance;
	
	private $widgets = null;
	private $checkin = null;
	private $cache = null;
	private $icon_url = null;
	
	/**
	 * Class constructor
	 */
	
	private function __construct () {
		add_shortcode('wp_quadratum', array($this, 'map_shortcode'));
		add_shortcode('wp_quadratum_map', array($this, 'map_shortcode'));
		add_shortcode('wpq_map', array($this, 'map_shortcode'));

		add_shortcode('wp_quadratum_locality', array($this, 'locality_shortcode'));
		add_shortcode('wpq_locality', array($this, 'locality_shortcode'));
		
		$this->hook('wp_loaded');
		$this->hook('wp_enqueue_scripts');
	}

	public static function get_instance() {
		if (!isset(self::$instance)) {
			$class = __CLASS__;
			self::$instance = new $class();
		}
		return self::$instance;
	}

	function wp_loaded() {
		$this->get_widgets();
	}
	

	/**
	 * Create the HTML and Javascript for the checkin map
	 */

	function render_checkin_map ($args, $shortcode=false) {
		// $args = array (
		//		'width' =>
		//		'height' =>
		//		'zoom' =>
		//		'container-class' =>
		//		'container-id' =>
		//		'map-class' =>
		//		'map-id' =>
		//		'venue-class' =>
		//		'checkin' =>
		//	)

		$provider = WP_Quadratum::get_option ('provider');
		$content = array ();

		if ($this->checkin) {
			$venue = $this->checkin->venue;
			$location = $venue->location;
			$venue_url = 'https://foursquare.com/v/' . $venue->id;

			$style = 'style="width:' . $args['width'] . $args['width_units'] . '"';
			$content[] = '<div id="' . $args['container-id'] . '" class="' . $args['container-class'] .'" ' . $style . '>';

			$style = 'style="max-width:none; position:relative; width:' . $args['width'] . $args['width_units'] . '; height:' . $args['height'] . $args['height_units']. ';"';
			$content[] = '<div id="' . $args['map-id'] . '" class="' . $args['map-class'] . '" ' . $style . '></div>';
			$content[] = '<div class="' . $args['venue-class'] . '">';

			$params = array (
				'venue-url' => $venue_url,
				'venue-name' => $venue->name,
				'checked-in-at' => $this->checkin->createdAt
			);

			$strapline = '<h5>Last seen at <a href="' . $venue_url . '?ref=' . WP_Quadratum::get_option('client_id') . '" target="_blank">' . $venue->name . '</a> on ' . date ("d M Y G:i T", $this->checkin->createdAt) . '</h5>';

			apply_filters('wp_quadratum_checkin', $this->checkin);

			$content[] = apply_filters ('wp_quadratum_strapline', $strapline, $params);

			$content[] = '</div>';
			$content[] = '</div>';

			if ($shortcode) {
				$content[] = '<form id="' . $args['form-id'] .'">';
				$content[] = '<input type="hidden" id="' . $args['zoom-id'] . '" value="' . $args['zoom'] . '"/>';
				$content[] = '</form>';
			}
		}

		else {
			$content[] = '<h5>Something went wrong; the Foursquare API might be down?</h5>';
		}
		
		return $content;
	}

	/**
	 * Shortcode handler for the [wp_quadratum] and [wpq_map] shortcodes; expands the
	 * shortcode to the checkin map according to the current set of plugin settings/options.
	 *
	 * @param array atts Array containing the optional shortcode attributes specified by
	 * the current instance of the shortcode.
	 * @param string content String containing the enclosed content when the shortcode is
	 * specified in the enclosing form. If the self-closing form is used, this parameter will
	 * default to null.
	 * @return string String containing the checkin map, providing that the current set
	 * of settings/options permit this.
	 */

	function map_shortcode ($atts, $content=null) {
		if ($options['enable_map_sc'] === 'on') {
			// TODO: handle self-closing and enclosing shortcode forms properly
			// TODO: this function is fugly; need to break out the checkin acquisition
			// and map generation code into a function/functions that can be called by
			// both the shortcode, the widget and the the_content filter (when I write it)
			// TODO: check and handle error responses from the 4sq API
			// TODO: handle 4sq API response caching
		
			static $instance = 0;

			$container_id = 'wp-quadratum-shortcode-container-' . $instance;
			$map_id = 'wp-quadratum-shortcode-map-' . $instance;
			$form_id = 'wp-quadratum-shortcode-form-' . $instance;
			$zoom_id = 'wp-quadratum-shortcode-zoom-' . $instance;
			$content = array ();
		
			extract (shortcode_atts (array (
				'width' => 300,
				'width_units' => 'px',
				'height' => 300,
				'height_units' => 'px',
				'zoom' => 16
			), $atts));

			if (strpos($width_units, 'px') === false && strpos($width_units, '%') === false) {
				$width_units = 'px';
			}
			if (strpos($height_units, 'px') === false && strpos($height_units, '%') === false) {
				$height_units = 'px';
			}

			$args = array ();
			$args['width'] = $width;
			$args['width_units'] = $width_units;
			$args['height'] = $height;
			$args['height_units'] = $height_units;
			$args['zoom'] = $zoom;
			$args['container-class'] = 'wp-quadratum-shortcode-container';
			$args['container-id'] = $container_id;
			$args['map-class'] = 'wp-quadratum-shortcode-map';
			$args['map-id'] = $map_id;
			$args['venue-class'] = 'wp-quadratum-shortcode-venue';
			$args['form-id'] = $form_id;
			$args['zoom-id'] = $zoom_id;
			$args['instance'] = $instance;
			$content = WP_QuadratumFrontEnd::render_checkin_map ($args, true);
			$instance++;

			$content = implode (PHP_EOL, $content);
		}
		
		return $content;
	}
	
	function locality_shortcode($atts, $content=null) {
		$options = WP_Quadratum::get_option();
		if ($options['enable_locality_sc'] === 'on' &&
				!empty($options['factual_oauth_key']) &&
				!empty($options['factual_oauth_secret'])) {
			extract (shortcode_atts (array (
				'type' => 'locality'
			), $atts));

			$type = strtolower($type);

			if ($this->checkin) {
				$value = __('No information currently available', 'wp-quadratum');
				$location = null;
				if (isset($this->checkin) && isset($this->checkin->venue) && isset($this->checkin->venue->location)) {
					$location = $this->checkin->venue->location;
				}
				
				switch ($type) {
					case 'venue':
						if (isset($this->checkin) && isset($this->checkin->venue) && isset($this->checkin->venue->name)) {
							$value = $this->checkin->venue->name;
						}
						break;
						
					case 'address':
						if (isset($location) && isset($location->address)) {
							$value = $location->address;
						}
						else if (isset($location) && isset($location->lat) && isset($location->lng)) {
							$cache = $this->get_reverse_geocode($location);
							if (isset($cache['address'])) {
								$value = $cache['address'];
							}
						}
						break;

					case 'region':
						if (isset($location) && isset($location->lat) && isset($location->lng)) {
							$cache = $this->get_reverse_geocode($location);
							if (isset($cache['region'])) {
								$value = $cache['region'];
							}
						}
						break;
						
					case 'postcode':
						if (isset($location) && isset($location->lat) && isset($location->lng)) {
							$cache = $this->get_reverse_geocode($location);
							if (isset($cache['postcode'])) {
								$value = $cache['postcode'];
							}
						}
						break;
						
					case 'coordinates':
						if (isset($location) && isset($location->lat) && isset($location->lng)) {
							$value = $location->lat . ',' . $location->lng;
						}
						break;

					case 'timezone':
						if (isset($this->checkin) && isset($this->checkin->timeZone)) {
							$value = $this->checkin->timeZone;
						}
						break;
						
					case 'tzoffset':
						if (isset($this->checkin) && isset($this->checkin->timeZoneOffset)) {
							$tzo = $this->checkin->timeZoneOffset;
							$minus = strpos($tzo, '-');
							if ($minus === 0) {
								$tzo = substr($tzo, $minus, 1);
								$minus = '-';
							}
							else {
								$minus = '+';
							}
							$tzo = $tzo / 60;
							$value = 'GMT' . $minus . $tzo;
						}
						break;
						
					case 'locality':
					default:
						$type = 'locality';
						if (isset($location) && isset($location->city)) {
							$value = $location->city;
						}
						else if (isset($location) && isset($location->lat) && isset($location->lng)) {
							$cache = $this->get_reverse_geocode($location);
							$value = $cache['locality'];
						}
						break;
				}

				$content = '<span class="wp-quadratum-'
					. $type . '">'
					. apply_filters('wp_quadratum_locality', $value, $type)
					. '</span>';

			}
		}

		return $content;
	}
	
	private function get_reverse_geocode($location) {
		if ($this->cache === null) {
			require_once(FACTUAL_DRIVER_SRC);
			
			$options = WP_Quadratum::get_option();
			$factual = new Factual($options['factual_oauth_key'], $options['factual_oauth_secret']);
			$point = new FactualPoint($location->lat, $location->lng);
			try {
				//$start = microtime(true);
				$response = $factual->factualReverseGeocode($point);
				$this->cache = $response[0];
				WP_Quadratum::set_cache(WP_Quadratum::LOCALITY_CACHE, $this->cache);
			}
			
			catch (FactualApiException $e) {
				$this->cache = WP_Quadratum::get_cache(WP_Quadratum::LOCALITY_CACHE);
			}
			//$end = (microtime(true) - $start);
			//error_log('Cache refreshed in ' . $end . ' secs');
		}
		
		return $this->cache;
	}
	
	public function wp_enqueue_scripts() {
		$handle = 'wp-quadratum-frontend-script';
		$src = WPQUADRATUM_URL . 'js/wp-quadratum-frontend';
		$src = WP_Quadratum::make_js_path($src);
		$deps = array('mapstraction-js', 'wp-mapstraction-js');
		$ver = false;
		$footer = true;

		wp_enqueue_script($handle, $src, $deps, $ver, $footer);

		// Code Health Warning
		// This is fugly ... if the current page is trying to display a checkin map via
		// the plugin's shortcode *only*, then we may well have no widgets defined and
		// thus, by the time the shortcode is processed (within the Loop), there will be
		// no checkin data present, so for now, we'll have to query Foursquare for each page
		// load.
		//

		//if (count($this->widgets) !== 0) {
		$have_checkin = false;
		$cached_checkin = false;
		
		$json = WP_Quadratum::get_foursquare_checkins ();
		if (!$this->validate_checkin($json)) {
			$json = WP_Quadratum::get_cache(WP_Quadratum::CHECKIN_CACHE);
			if ($this->validate_checkin($json)) {
				$have_checkin = true;
				$cached_checkin = true;
			}
		}
		else {
			$have_checkin = true;
		}
		
		if ($have_checkin) {
			$checkins = $json->response->checkins->items;

			$provider = WP_Quadratum::get_option ('provider');

			$venue = $checkins[0]->venue;
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

			$args = array(
				'provider' => $provider,
				'icon-url' => $icon_url,
				'widgets' => $this->widgets,
				'checkin' => $checkins[0]
			);
			$this->checkin = $checkins[0];
			$this->icon_url = $icon_url;

			wp_localize_script($handle, 'WPQuadratum', $args);
			
			if (!$cached_checkin) {
				WP_Quadratum::set_cache(WP_Quadratum::CHECKIN_CACHE, $json);
			}
		}
		//}
	}
	
	private function validate_checkin($json) {
		if ($json === false || $json === null) {
			return false;
		}
		
		if (!isset($json->response) || !isset($json->response->checkins) || !isset($json->response->checkins->items)) {
			return false;
		}
		
		return true;
	}

	private function get_widgets() {
		$this->widgets = array();
		
		// A widget is referenced in the WP back-end by the lowercased version of
		// the argument passed to register_widget. In wp-quadratum.php we call ...
		//
		// register_widget ('WP_QuadratumWidget');
		//
		// ... from the widgets_init hook, thus WP_QuadratumWidget is referenced
		// as wp_quadratumwidget.
		
		// The settings for all instances of a widget (active and inactive) are
		// stored in a serialised array in the wp_options table called widget_'reference'.
		// So the settings for all instances of WP_QuadratumWidget are contained in
		// 'widget_wp_quadratumwidget'
		
		// Get a copy of this widget's settings; bale if we can't find the settings
		
		$options = get_option('widget_wp_quadratumwidget');
		if (!is_array($options) || count($options) === 0) {
			error_log('Can\'t find the options for widget_wp_quadratum_widget in wp_options');
			error_log('Maybe something\'s changed in this version of WordPress?');
			return;
		}
		
		// The master list of all widgets, inactive and active are stored in a serialised
		// array, also in wp_options, called 'sidebars_widgets'. Get a cop of this and bale
		// if we can't find it ...
		
		$widgets = get_option('sidebars_widgets');
		if (!is_array($widgets) || count($widgets) === 0) {
			error_log('Can\'t find the options for sidebars_widgets in wp_options');
			error_log('Maybe something\'s changed in this version of WordPress?');
			return;
		}
		
		foreach ($widgets as $key => $val) {
			// Inactive widgets are stored in an array keyed on 'wp_inactive_widgets'.
			// The version number for the master widget list is keyed on 'array_version'.
			// Skip both of these keys ...
			
			if ($key == self::INACTIVE_WIDGETS || $key == self::WIDGET_VERSION) {
				continue;
			}
			
			// The remaining elements of the master list are keyed on the sidebar id,
			// one element per defined sidebar (which may or may not bear some resemblance
			// to the HTML id for the sidebar, but this is theme dependent).
			//
			// If a sidebar has no active widgets, the key's value will be an empty array.
			//
			// If a sidebar has active widgets, the key's value will be an array of widget
			// names, in the form 'name-id' where 'id' is the instance id for this
			// widget instance and also the key for this instance's settings in the
			// widget's settings array, held in $options above ...
			
			if (is_array($val) && (count($val) !== 0)) {
				foreach ($val as $index => $widget_id) {
					$pos = strpos($widget_id, self::ID_PREFIX);
					if ($pos !== false && $pos === 0) {
						$pos = strpos($widget_id, '-');
						if ($pos !== false) {
							$widget_name = substr($widget_id, 0, $pos);
							$widget_inst = substr($widget_id, ++$pos);
							
							if (array_key_exists($widget_inst, $options)) {
								$this->widgets[] = array(
									'id' => $widget_id,
									'name' => $widget_name,
									'instance' => $widget_inst,
									'options' => $options[$widget_inst]
								);
							}
						}
					}
				}	// end-foreach (...)
			}
		}
	}
	
}	// end class WP_QuadratumFrontEnd

WP_QuadratumFrontEnd::get_instance();