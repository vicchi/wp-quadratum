<?php

if (!class_exists('WP_Quadratum')) {
	class WP_Quadratum extends WP_PluginBase_v1_1 {
		private static $instance;
	
		const OPTIONS = 'wp_quadratum_settings';
		const CACHE = 'wp_quadratum_cache';
		const LOCALITY_CACHE = 'locality';
		const CHECKIN_CACHE = 'checkin';
		const VERSION = '1311';
		const DISPLAY_VERSION = 'v1.3.1';
	
		/**
		 * Class constructor
		 */
	
		private function __construct () {
			$this->hook ('plugins_loaded');
		}
	
		public static function get_instance() {
			if (!isset(self::$instance)) {
				$class = __CLASS__;
				self::$instance = new $class();
			}
			return self::$instance;
		}

		static function make_redirect_url () {
			return plugins_url ()
				. '/'
				. dirname (plugin_basename (__FILE__))
				. '/wp-quadratum-callback.php';
		}

		/**
		 * Helper function to create the plugin's settings link hook
		 */

		static function make_settings_link () {
			return 'plugin_action_links_' . WPQUADRATUM_NAME;
		}
	
		/**
		 * "plugins_loaded" action hook; called after all active plugins and pluggable functions
		 * are loaded.
		 *
		 * Adds front-end display actions, shortcode support and admin actions.
		 */
	 
		function plugins_loaded () {
			register_activation_hook (__FILE__, array ($this, 'add_settings'));
		
			$this->hook ('init');
			$this->hook ('widgets_init');
		
			if (is_admin ()) {
				// For admin_init, admin_menu, admin_print_styles, admin_print_scripts and
				// plugin_action_links hooks, now see includes/wp-quadratum-admin.php

				require_once (WPQUADRATUM_ADMIN_SRC);
			}

			else {
				// For wp_head and wp_head_scripts hooks and for shortcode support, now see
				// includes/wp-quadratum-frontend.php

				require_once (WPQUADRATUM_FRONTEND_SRC);
			
				$options = WP_Quadratum::get_option();
				$map = $options['provider'];
				$maps = WP_Mapstraction::get_instance()->get_supported_maps();
				if ($maps[$map]['script']['auth']) {
					$hook = 'mapstraction-' . $map . '-auth';
					$hook_func = $map . '_auth';
					$this->hook($hook, $hook_func);
				}

				WP_Mapstraction::get_instance()->set_footer(true);
				WP_Mapstraction::get_instance()->add_map($map);
			
			}
		}
	
		function nokia_auth() {
			$options = WP_Quadratum::get_option();
			return (array('app-id' => $options['nokia_app_id'],
				'auth-token' => $options['nokia_app_token']));
		}
	
		function googlev3_auth() {
			$options = WP_Quadratum::get_option();
			return (array('key' => $options['google_key'],
				'sensor' => $options['google_sensor']));
		}
	
		function microsoft7_auth() {
			$options = WP_Quadratum::get_option();
			return (array('key' => $options['microsoft7_key']));
		}
	
		function openmq_auth() {
			$options = WP_Quadratum::get_option();
			return (array('key' => $options['openmq_key']));
		}
	
		/**
		 * "wp_mxn_helper_providers" filter hook; called to trim the list of Mapstraction
		 * providers that WP MXN Helper supports to the list that this plugin currently
		 * supports
		 */
	
		/*function trim_mapstraction_providers ($providers) {
			//$plugin_providers = array ('nokia', 'googlev3', 'leaflet', 'openmq', 'cloudmade', 'openlayers');
			$plugin_providers = array ('nokia', 'googlev3', 'cloudmade', 'openlayers');
			$trimmed_providers = array ();
			foreach ($providers as $pname => $pchar) {
				if (in_array ($pname, $plugin_providers)) {
					$trimmed_providers[$pname] = $pchar;
				}
			}
		
			return $trimmed_providers;
		}*/
	
		/**
		 * "init" action hook; called to initialise the plugin
		 */

		function init () {
			$lang_dir = basename (dirname (__FILE__)) . DIRECTORY_SEPARATOR . 'lang';
			load_plugin_textdomain ('wp-quadratum', false, $lang_dir);
		}
	
		/**
		 * "widgets_init" action hook; called to initialise the plugin's widget(s)
		 */

		function widgets_init () {
			return register_widget ('WP_QuadratumWidget');
		}
	
		/**
		 * plugin activation / "activate_pluginname" action hook; called when the plugin is
		 * first activated.
		 *
		 * Defines and sets up the default settings and options for the plugin. The default set
		 * of options are configurable, at activation time, via the
		 * 'wp_quadratum_default_settings' filter hook.
		 */

		static function add_settings () {
			$settings = WP_Quadratum::get_option ();

			if (!is_array ($settings)) {
				$settings = apply_filters ('wp_quadratum_default_settings',
					array (
						"installed" => "on",
						"version" => self::VERSION,
						"client_id" => "",
						"client_secret" => "",
						"oauth_token" => "",
						"provider" => "nokia",
						"nokia_app_id" => "",
						"nokia_app_token" => "",
						"google_key" => "",
						"google_sensor" => "false",
						//"cloudmade_key" => "",
						'openmq_key' => '',
						'bingv7_key' => '',
						'enable_map_sc' => 'on',
						'enable_locality_sc' => '',
						'factual_oauth_key' => '',
						'factual_oauth_secret' => ''
						)
					);

				update_option (self::OPTIONS, $settings);
			}
			
			$cache = WP_Quadratum::get_cache();
			if (!is_array($cache)) {
				$cache = array(
					'timestamp' => time(),
					'checkin' => null,
					'locality' => null
				);
				update_option(self::CACHE, $cache);
			}
		}
	
		/**
		 * Queries the back-end database for WP Quadratum settings and options.
		 *
		 * @param string $key Optional settings/options key name; if specified only the value
		 * for the key will be returned, if the key exists, if omitted all settings/options
		 * will be returned.
		 * @return mixed If $key is specified, a string containing the key's settings/option 
		 * value is returned, if the key exists, else an empty string is returned. If $key is
		 * omitted, an array containing all settings/options will be returned.
		 */

		static function get_option () {
			$num_args = func_num_args ();
			$options = get_option (self::OPTIONS);

			if ($num_args > 0) {
				$args = func_get_args ();
				$key = $args[0];
				$value = "";
				if (isset ($options[$key])) {
					$value = $options[$key];
				}
				return $value;
			}
		
			else {
				return $options;
			}
		}
		
		static function get_cache($key=NULL) {
			$cache = get_option(self::CACHE);

			if (isset($key) && !empty($key)) {
				return json_decode($cache[$key]);
			}
			
			else {
				return $cache;
			}
		}

		/**
		 * Adds/updates a settings/option key and value in the back-end database.
		 *
		 * @param string key Settings/option key to be created/updated.
		 * @param string value Value to be associated with the specified settings/option key
		 */

		static function set_option($key, $value) {
			$options = get_option(self::OPTIONS);
			$options[$key] = $value;
			update_option(self::OPTIONS, $options);
		}

		static function set_cache($key, $value) {
			$cache = get_option(self::CACHE);
			$cache['timestamp'] = time();
			$cache[$key] = json_encode($value);
			update_option(self::CACHE, $cache);
		}

		/**
		 * Helper function to determine if debugging is enabled in WordPress and/or
		 * the plugin.
		 */

		static function is_debug() {
			return ((defined('WP_DEBUG') && WP_DEBUG == true) ||
					(defined('WPQUADRATUM_DEBUG') && WPQUADRATUM_DEBUG == true));
		}

		/**
		 * Helper function to make a style filename load debug or minimized CSS depending
		 * on the setting of WP_DEBUG and/or WPQUADRATUM_DEBUG.
		 */
	
		static function make_css_path($stub) {
			if (WP_Quadratum::is_debug()) {
				return $stub . '.css';
			}
		
			return $stub . '.min.css';
		}

		/**
		 * Helper function to make a script filename load debug or minimized JS depending
		 * on the setting of WP_DEBUG and/or WPQUADRATUM_DEBUG.
		 */
	
		static function make_js_path($stub) {
			if (WP_Quadratum::is_debug()) {
				return $stub . '.js';
			}
		
			return $stub . '.min.js';
		}

		/**
		 * Helper function to get the current checkin from the Foursquare API
		 */

		static function get_foursquare_checkins () {
			$client_id = WP_Quadratum::get_option ('client_id');
			$client_secret = WP_Quadratum::get_option ('client_secret');
			$oauth_token = WP_Quadratum::get_option ('oauth_token');
			$redirect_url = WP_Quadratum::make_redirect_url ();
			$endpoint = "users/self/checkins";
			$params = array ('limit' => 1);

			$fsq = new FoursquareHelper_v1_0 ($client_id, $client_secret, $redirect_url);
			$fsq->set_access_token ($oauth_token);
			$rsp = $fsq->get_private ($endpoint, $params);

			if ($rsp !== false) {
				$json = json_decode($rsp);
				return $json;
			}
			
			return $rsp;
		}

	}	// end-class WP_Quadratum
}	// end-if (!class_exists('WP_Quadratum))

WP_Quadratum::get_instance();

?>