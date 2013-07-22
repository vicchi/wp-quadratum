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
	
	/**
	 * Class constructor
	 */
	
	private function __construct () {
		//$this->mxn = new WP_MXNHelper_v2_0;
		//$this->mxn->register_callback ('cloudmade', array ($this, 'cloudmade_mxn_callback'));
		//$this->mxn->register_callback ('nokia', array ($this, 'nokia_mxn_callback'));
		//$this->mxn->register_callback ('googlev3', array ($this, 'googlev3_mxn_callback'));

		$provider = WP_Quadratum::get_option ('provider');
		//$this->mxn->set_frontend_providers (array ($provider));
		
		add_shortcode ('wp_quadratum', array ($this, 'shortcode'));

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
		//error_log('wp_quadratum_frontend::wp_loaded++');
		$this->get_widgets();
		//error_log('wp_quadratum_frontend::wp_loaded--');
	}
	
	/**
	 * WP MXN Helper callback; returns the configured CloudMade API key
	 */
	
	/*function cloudmade_mxn_callback () {
		$key = WP_Quadratum::get_option ('cloudmade_key');
		
		if (isset ($key)) {
			return (array ('key' => $key));
		}
	}*/
	
	/**
	 * WP MXN Helper callback; returns the configured Nokia API keys
	 */

	/*function nokia_mxn_callback () {
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
	}*/
	
	/**
	 * WP MXN Helper callback; returns the configured Google API key
	 */

	/*function googlev3_mxn_callback () {
		$key = WP_Quadratum::get_option ('google_key');
		$sensor = WP_Quadratum::get_option ('google_sensor');
		
		if (isset ($key) && isset ($sensor)) {
			return (array ('key' => $key, 'sensor' => $sensor));
		}
	}*/

	/**
	 * Create the HTML and Javascript for the checkin map
	 */

	function render_checkin_map ($args, $shortcode=false) {
		//error_log('wp-quadratum-frontend::render_checkin_map++');
		
		if ($this->checkin) {
			//error_log('Have stored checkin metadata');
		}
		else {
			//error_log('Cannot find stored checkin metadata');
		}
		
		// $args = array (
		//		'width' =>
		//		'height' =>
		//		'zoom' =>
		//		'private' =>
		//		'container-class' =>
		//		'container-id' =>
		//		'map-class' =>
		//		'map-id' =>
		//		'venue-class' =>
		//		'checkin' =>
		//	)

		$provider = WP_Quadratum::get_option ('provider');

		//$checkin = $args['checkin'];
		$venue = $this->checkin->venue;
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
		//$tab = "\t";

		/*$js = array ();
		$js[] = '<script type="text/javascript">';
		$js[] = "var id = document.getElementById ('" . $args['map-id'] . "');";
		$js[] = "var map = new mxn.Mapstraction (id, '" . $provider . "');";
		$js[] = 'var coords = new mxn.LatLonPoint (' . $location->lat . ',' . $location->lng . ');';
		$js[] = 'map.setCenterAndZoom (coords, ' . $args['zoom'] . ');';
		$js[] = "var opts = {icon: '" . $icon_url . "', iconSize: [32, 32]};";
		$js[] = 'var marker = new mxn.Marker (coords);';
		$js[] = 'marker.addData (opts);';
		$js[] = 'map.addMarker (marker);';
		$js[] = '</script>';*/

		$content[] = '<div id="' . $args['container-id'] . '" class="' . $args['container-class'] .'" style="width:' . $args['width'] . 'px;">';
		$content[] = '<div id="' . $args['map-id'] . '" class="' . $args['map-class'] . '" style="max-width: none; position: relative; width:' . $args['width'] . 'px; height:' . $args['height'] . 'px;"></div>';
		$content[] = '<div class="' . $args['venue-class'] . '">';
		
		$params = array (
			'venue-url' => $venue_url,
			'venue-name' => $venue->name,
			'checked-in-at' => $this->checkin->createdAt
		);
		
		$strapline = '<h5>Last seen at <a href="' . $venue_url . '" target="_blank">' . $venue->name . '</a> on ' . date ("d M Y G:i T", $this->checkin->createdAt) . '</h5>';
		$content[] = apply_filters ('wp_quadratum_strapline', $strapline, $params);
		
		$content[] = '</div>';
		$content[] = '</div>';
		
		if ($shortcode) {
			$content[] = '<form id="' . $args['form-id'] .'">';
			$content[] = '<input type="hidden" id="' . $args['zoom-id'] . '" value="' . $args['zoom'] . '"/>';
			$content[] = '</form>';
		}

		//error_log('wp-quadratum-frontend::render_checkin_map--');
		//return array_merge ($content, $js);
		return $content;
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
		//error_log('wp-quadratum-frontend::shortcode++');
		
		// TODO: handle self-closing and enclosing shortcode forms properly
		// TODO: this function is fugly; need to break out the checkin acquisition
		// and map generation code into a function/functions that can be called by
		// both the shortcode, the widget and the the_content filter (when I write it)
		// TODO: check and handle error responses from the 4sq API
		// TODO: handle 4sq API response caching
		
		static $instance = 0;

		if ($this->checkin) {
			//error_log('Have stored checkin metadata');
		}
		else {
			//error_log('Cannot find stored checkin metadata');
		}

		
		$container_id = 'wp-quadratum-shortcode-container-' . $instance;
		$map_id = 'wp-quadratum-shortcode-map-' . $instance;
		$form_id = 'wp-quadratum-shortcode-form-' . $instance;
		$zoom_id = 'wp-quadratum-shortcode-zoom-' . $instance;
		$content = array ();
		
		extract (shortcode_atts (array (
			'width' => 300,
			'height' => 300,
			'zoom' => 16,
			'private' => false
		), $atts));

		//$json = WP_Quadratum::get_foursquare_checkins ();
		//$checkins = $json->response->checkins->items;

		//foreach ($checkins as $checkin) {
			/*$app_id = NULL;
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
			}*/
		
			$args = array ();
			$args['width'] = $width;
			$args['height'] = $height;
			$args['zoom'] = $zoom;
			$args['private'] = $private;
			//$args['app-id'] = $app_id;
			//$args['app-token'] = $app_token;
			$args['container-class'] = 'wp-quadratum-shortcode-container';
			//$args['container-id'] = 'wp-quadratum-container-' . $instance;
			$args['container-id'] = $container_id;
			$args['map-class'] = 'wp-quadratum-shortcode-map';
			//$args['map-id'] = 'wp-quadratum-map-' . $instance;
			$args['map-id'] = $map_id;
			$args['venue-class'] = 'wp-quadratum-shortcode-venue';
			$args['form-id'] = $form_id;
			$args['zoom-id'] = $zoom_id;
			$args['instance'] = $instance;
			//$args['checkin'] = $checkin;
			$content = WP_QuadratumFrontEnd::render_checkin_map ($args, true);
			$instance++;

			//break;	// Not really needed as we only return a single checkin item
		//}

		//error_log('wp-quadratum-frontend::shortcode--');
		return implode (PHP_EOL, $content);
	}
	
	public function wp_enqueue_scripts() {
		//error_log('wp-quadratum-frontend::wp_enqueue_scripts++');
		
		$handle = 'wp-quadratum-frontend-script';
		$src = WPQUADRATUM_URL . 'js/wp-quadratum-frontend.js';
		$deps = array('mapstraction-js', 'wp-mapstraction-js');
		$ver = false;
		$footer = true;

		//error_log('Enqueueing handle: ' . $handle . ', deps: '. implode(',', $deps) . ', src: ' . $src);
		wp_enqueue_script($handle, $src, $deps, $ver, $footer);

		if (count($this->widgets) !== 0) {
			//error_log('Getting 4Sq checkins');
			$json = WP_Quadratum::get_foursquare_checkins ();
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
			//error_log('Storing checkin metadata');
			$this->checkin = $checkins[0];
			
			//error_log('Localising ' . $handle . ' as WPQuadratum');
			//wp_localize_script($handle, 'WPQuadratum', $this->widgets);
			wp_localize_script($handle, 'WPQuadratum', $args);
		}
		//error_log('wp-quadratum-frontend::wp_enqueue_scripts--');
	}
	
	private function get_widgets() {
		//error_log('wp-quadratum-frontend::get_widgets++');
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
					//error_log('[' . $index . '] = ' . $widget_id);
					$pos = strpos($widget_id, self::ID_PREFIX);
					if ($pos !== false && $pos === 0) {
						//error_log('Selected widget: ' . $widget_id);
						$pos = strpos($widget_id, '-');
						if ($pos !== false) {
							//error_log('Well formed widget name');
							$widget_name = substr($widget_id, 0, $pos);
							//error_log('Widget name: ' . $widget_name);
							$widget_inst = substr($widget_id, ++$pos);
							//error_log('Widget instance: ' . $widget_inst);
							
							if (array_key_exists($widget_inst, $options)) {
								//error_log('Found widget settings in $options');
								//$this->widgets[$widget_id] = array(
								$this->widgets[] = array(
									'id' => $widget_id,
									'name' => $widget_name,
									'instance' => $widget_inst,
									'options' => $options[$widget_inst]
								);
							}
							else {
								//error_log('Cannot find widget settings');
							}
						}
						else {
							//error_log('Cannot find \'-\' character in ' . $widget_id);
						}
					}
					else {
						//error_log('Discarded widget: ' . $widget_id);
					}
				}	// end-foreach (...)
			}
			else {
				//error_log('Skipping ' . $key . ' due to empty value');
			}
		}
		//error_log('wp-quadratum-frontend::get_widgets--');
	}
	
}	// end class WP_QuadratumFrontEnd

WP_QuadratumFrontEnd::get_instance();