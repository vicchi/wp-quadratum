<?php

define('WPMAPSTRACTION_URL', plugin_dir_url(__FILE__));
define('WPMAPSTRACTION_PATH', plugin_dir_path(__FILE__));
//define ('WPMAPSTRACTION_DEBUG', true);


if (!class_exists('WP_Mapstraction')) {
	class WP_Mapstraction extends WP_PluginBase_v1_1 {
		
		private static $instance;
		private $maps;
		private $core;
		private $display;
		private $auth;
		private $params;
		private $footer = false;
		
		private function __construct() {
			$this->display = array();
			
			$this->core = array(
				'handle' => 'mapstraction-js',
				'src' => 'http://mapstraction.com/mxn/build/dev/mxn.js?(%s)',
				'deps' => array('googlev3-js', 'nokia-js', 'leaflet-js', 'microsoft7-js', 'openlayers-js', 'openmq-js')
				);
				
			$this->maps = array(
				'googlev3' => array(
					'description' => 'Google v3',
					'meta' => null,
					'style' => null,
					'script' => array(
						'handle' => 'googlev3-js',
						'src' => 'https://maps.googleapis.com/maps/api/js?key=%s&sensor=%s',
						'deps' => false,
						'auth' => true,
						'auth-type' => 'url'
						)
					),
				'nokia' => array(
					'description' => 'HERE',
					'meta' => '<meta http-equiv="X-UA-Compatible" content="IE=7; IE=EmulateIE9" />',
					'style' => null,
					'script' => array(
						'handle' => 'nokia-js',
						'src' => 'http://api.maps.nokia.com/2.2.4/jsl.js',
						'deps' => false,
						'auth' => true,
						'auth-type' => 'js'
						)
					),
				'leaflet' => array(
					'description' => 'Leaflet',
					'meta' => null,
					'style' => array(
						'leaflet-css' => array(
							'handle' => 'leaflet-css',
							'src' => 'http://cdn.leafletjs.com/leaflet-0.5/leaflet.css',
							'deps' => false,
							'conditional' => null
							),
						'leaflet-ie-css' => array(
							'handle' => 'leaflet-ie-css',
							'src' => 'http://cdn.leafletjs.com/leaflet-0.5/leaflet.ie.css',
							'deps' => array('leaflet-css'),
							'conditional' => 'lte IE 8'
							)
						),
					'script' => array(
						'handle' => 'leaflet-js',
						'src' => 'http://cdn.leafletjs.com/leaflet-0.5/leaflet.js',
						'deps' => false,
						'auth' => false,
						'auth-type' => null
						)
					),
				'microsoft7' => array(
					'description' => 'Bing Maps v7.0',
					'meta' => null,
					'style' => null,
					'script' => array(
						'handle' => 'microsoft7-js',
						'src' => 'http://ecn.dev.virtualearth.net/mapcontrol/mapcontrol.ashx?v=7.0',
						'deps' => false,
						'auth' => true,
						'auth-type' => 'js'
						)
					),
				'openlayers' => array(
					'description' => 'OpenLayers',
					'meta' => null,
					'style' => null,
					'script' => array(
						'handle' => 'openlayers-js',
						'src' => 'http://dev.openlayers.org/releases/OpenLayers-2.12/OpenLayers.js',
						'deps' => false,
						'auth' => false,
						'auth-type' => null
						)
					),
				'openmq' => array(
					'description' => 'MapQuest Open',
					'meta' => null,
					'style' => null,
					'script' => array(
						'handle' => 'openmq-js',
						'src' => 'http://open.mapquestapi.com/sdk/js/v7.0.s/mqa.toolkit.js?key=%s',
						'deps' => false,
						'auth' => true,
						'auth-type' => 'url'
						)
					)
				);
				
			$this->hook('wp_enqueue_scripts');
		}
		
		public static function get_instance() {
			if (!isset(self::$instance)) {
				$class = __CLASS__;
				self::$instance = new $class;
			}
			return self::$instance;
		}
		
		public function set_footer($footer) {
			$this->footer = $footer;
		}
		
		public function wp_enqueue_scripts() {
			$deps = array();
			foreach ($this->display as $map => $args) {
				$deps[] = $this->maps[$map]['script']['handle'];
			}

			$src = sprintf($this->core['src'], implode(',', array_keys($this->display)));
			$handle = $this->core['handle'];
			$footer = $this->footer;
			wp_enqueue_script($handle, $src, $deps, null, $footer);
			
			foreach ($this->display as $map => $status) {
				$this->enqueue_script($map);
			}

			$handle = 'wp-mapstraction-css';
			$src = WPMAPSTRACTION_URL . 'css/wp-mapstraction';
			$src = WP_Mapstraction::make_css_path($src);
			$deps = array();
			$ver = false;
			$media = 'all';
			wp_enqueue_style($handle, $src, $deps, $ver, $media);

			$handle = 'wp-mapstraction-js';
			$src = WPMAPSTRACTION_URL . 'js/wp-mapstraction';
			$src = WP_Mapstraction::make_js_path($src);
			$deps = array('jquery');
			
			wp_enqueue_script($handle, $src, $deps, null, $footer);
			
			if (count($this->auth) !== 0) {
				wp_localize_script($handle, 'WPMapstraction', $this->auth);
			}
		}
		
		private function enqueue_script($map) {
			$params = null;
			
			if ($this->maps[$map]['script']['auth']) {
				$func = $map . '_auth';
				if (method_exists($this, $func)) {
					$params = call_user_func(array($this, $func));
					
					if ($this->maps[$map]['script']['auth-type'] === 'js') {
						$this->auth[$map] = $params;
					}
				}
			}

			if (!empty($this->maps[$map]['meta'])) {
				echo $this->maps[$map]['meta'] . PHP_EOL;
			}

			if (!empty($this->maps[$map]['style'])) {
				foreach ($this->maps[$map]['style'] as $handle => $vars) {
					$src = $vars['src'];
					$deps = $vars['deps'];
					wp_enqueue_style($handle, $src, $deps);
					
					if ($vars['conditional']) {
						global $wp_styles;
						$wp_styles->add_data($handle, 'conditional', $vars['conditional']);
					}
				}
			}
			
			$handle = $this->maps[$map]['script']['handle'];
			$src = $this->maps[$map]['script']['src'];
			
			if ($this->maps[$map]['script']['auth-type'] === 'url' && $params) {
				if (!isset($params['sensor'])) {
					$params['sensor'] = 'false';
				}
				$src = sprintf($src, $params['key'], $params['sensor']);
			}
			
			$deps = $this->maps[$map]['script']['deps'];
			$footer = $this->footer;
			wp_enqueue_script($handle, $src, $deps, null, $footer);
		}

		public function add_map($map) {
			if (isset($map) && !empty($map) && array_key_exists($map, $this->maps)) {
				$this->display[$map] = true;
			}
		}
		
		public function add_maps($maps) {
			foreach ($maps as $map) {
				$this->add_map($map);
			}
		}
		
		public function remove_map($map) {
			if (isset($map) && !empty($map) && array_key_exists($map, $this->maps)) {
				if (array_key_exists($this->display[$map])) {
					unset($this->display[$map]);
				}
			}
		}
		
		public function remove_maps($maps) {
			foreach ($maps as $map) {
				$this->remove_map($map);
			}
		}
		
		public function get_supported_maps() {
			return $this->maps;
		}
		
		public function add_auth_func($map, $func) {
			if ($this->check_map($map)) {
				if ($this->maps[$map]['auth']) {
					$this->maps[$map]['auth'] = $func;
					return true;
				}
			}
			return false;
		}
		private function check_map($map) {
			if (isset($map) && !empty($map)) {
				return array_key_exists($map, $this->maps);
			}
			return false;
		}
		
		private function googlev3_auth() {
			$params = array('key' => null, 'sensor' => 'false');
			return apply_filters('mapstraction-googlev3-auth', $params);
		}
		
		private function nokia_auth() {
			$params = array('app-id' => null, 'auth-token' => null);
			return apply_filters('mapstraction-nokia-auth', $params);
		}
		
		private function microsoft7_auth() {
			$params = array('key' => null);
			return apply_filters('mapstraction-microsoft7-auth', $params);
		}
		
		private function openmq_auth() {
			$params = array('key' => null);
			return apply_filters('mapstraction-openmq-auth', $params);
		}
		
		/**
		 * Helper function to determine if debugging is enabled in WordPress and/or
		 * the plugin.
		 */

		static function is_debug() {
			return ((defined('WP_DEBUG') && WP_DEBUG == true) ||
					(defined('WPMAPSTRACTION_DEBUG') && WPMAPSTRACTION_DEBUG == true));
		}

		/**
		 * Helper function to make a style filename load debug or minimized CSS depending
		 * on the setting of WP_DEBUG and/or WPMAPSTRACTION_DEBUG.
		 */

		static function make_css_path($stub) {
			if (WP_Mapstraction::is_debug()) {
				return $stub . '.css';
			}

			return $stub . '.min.css';
		}

		/**
		 * Helper function to make a script filename load debug or minimized JS depending
		 * on the setting of WP_DEBUG and/or WPMAPSTRACTION_DEBUG.
		 */

		static function make_js_path($stub) {
			if (WP_Mapstraction::is_debug()) {
				return $stub . '.js';
			}

			return $stub . '.min.js';
		}
		
	}	// end-class WP_Mapstraction
}	// end-if (!class_exists(...))
?>