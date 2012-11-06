<?php
/*
Plugin Name: WP Quadratum
Plugin URI: http://www.vicchi.org/codeage/wp-quadratum/
Description: A WordPress plugin to display your last Foursquare checkin as a map widget, fully authenticated via OAuth 2.0.
Version: 1.2
Author: Gary Gale
Author URI: http://www.garygale.com/
License: GPL2
Text Domain: wp-quadratum
*/

define ('WPQUADRATUM_URL', plugin_dir_url (__FILE__));
define ('WPQUADRATUM_PATH', plugin_dir_path (__FILE__));
//define ('WPQUADRATUM_DEBUG', true);

/*
 * Determine WordPress directory constants.
 * See http://codex.wordpress.org/Determining_Plugin_and_Content_Directories
 */

if (!defined ('WP_CONTENT_URL'))
	define ('WP_CONTENT_URL', WP_SITEURL . '/wp-content');
if (!defined ('WP_CONTENT_DIR'))
	define ('WP_CONTENT_DIR', ABSPATH . 'wp-content');
if (!defined ('WP_PLUGIN_URL'))
	define ('WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins');
if (!defined ('WP_PLUGIN_DIR'))
	define ('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins');
if (!defined ('WPMU_PLUGIN_URL'))
	define ('WPMU_PLUGIN_URL', WP_CONTENT_URL. '/mu-plugins');
if (!defined ('WPMU_PLUGIN_DIR'))
	define ('WPMU_PLUGIN_DIR', WP_CONTENT_DIR . '/mu-plugins');

require_once (WPQUADRATUM_PATH . '/includes/wp-plugin-base/wp-plugin-base.php');
require_once (WPQUADRATUM_PATH . '/includes/wp-mxn-helper/wp-mxn-helper.php');
require_once (WPQUADRATUM_PATH . '/includes/wp-quadratum-widget.php');

define ('WPNAUTH_PLUGIN_HELPER', WP_PLUGIN_DIR . '/wp-nokia-auth/wp-nokia-auth-helper.php');
define ('WPNAUTH_PLUGIN_PATH', 'wp-nokia-auth/wp-nokia-auth.php');

if (file_exists (WPNAUTH_PLUGIN_HELPER)) {
	include_once (WPNAUTH_PLUGIN_HELPER);
}

include_once (ABSPATH . 'wp-admin/includes/plugin.php');

class WP_Quadratum extends WP_PluginBase_v1_1 {
	static $instance;
	
	const OPTIONS = 'wp_quadratum_settings';
	const VERSION = '120';
	const DISPLAY_VERSION = 'v1.2.0';
	
	/**
	 * Class constructor
	 */
	
	function __construct () {
		self::$instance = $this;
		
		$this->hook ('plugins_loaded');
	}

	/**
	 * Helper function to check whether the WP Nokia Auth plugin is installed
	 */
	
	static function is_wpna_installed () {
		return file_exists (WPNAUTH_PLUGIN_HELPER);
	}

	/**
	 * Helper function to check whether the WP Nokia Auth plugin is active
	 */

	static function is_wpna_active () {
		return is_plugin_active (WPNAUTH_PLUGIN_PATH);
	}

	/**
	 * Helper function to create the plugin's OAuth redirect URL
	 */

	static function make_redirect_url () {
		return plugins_url ()
			. '/'
			. dirname (plugin_basename (__FILE__))
			. '/includes/wp-quadratum-callback.php';
	}

	/**
	 * Helper function to create the plugin's settings link hook
	 */

	static function make_settings_link () {
		return 'plugin_action_links_' . plugin_basename (__FILE__);
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
		$this->hook ('wp_mxn_helper_providers', 'trim_mapstraction_providers');
		
		if (is_admin ()) {
			// For admin_init, admin_menu, admin_print_styles, admin_print_scripts and
			// plugin_action_links hooks, now see includes/wp-quadratum-admin.php

			require_once (WPQUADRATUM_PATH . '/includes/wp-quadratum-admin.php');
		}

		else {
			// For wp_head and wp_enqueue_scripts hooks and for shortcode support, now see
			// includes/wp-quadratum-frontend.php

			require_once (WPQUADRATUM_PATH . '/includes/wp-quadratum-frontend.php');
		}
	}
	
	/**
	 * "wp_mxn_helper_providers" filter hook; called to trim the list of Mapstraction
	 * providers that WP MXN Helper supports to the list that this plugin currently
	 * supports
	 */
	
	function trim_mapstraction_providers ($providers) {
		//$plugin_providers = array ('nokia', 'googlev3', 'leaflet', 'openmq', 'cloudmade', 'openlayers');
		$plugin_providers = array ('nokia', 'googlev3', 'cloudmade', 'openlayers');
		$trimmed_providers = array ();
		foreach ($providers as $pname => $pchar) {
			if (in_array ($pname, $plugin_providers)) {
				$trimmed_providers[$pname] = $pchar;
			}
		}
		
		return $trimmed_providers;
	}
	
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
					"cloudmade_key" => "",
					)
				);

			update_option (self::OPTIONS, $settings);
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

	/**
	 * Adds/updates a settings/option key and value in the back-end database.
	 *
	 * @param string key Settings/option key to be created/updated.
	 * @param string value Value to be associated with the specified settings/option key
	 */

	static function set_option ($key, $value) {
		$options = get_options (self::OPTIONS);
		$options[$key] = $value;
		update_option (self::OPTIONS, $options);
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
		$json = json_decode ($rsp);
		return $json;
	}

}	// end-class WP_Quadratum

$__wp_quadratum_instance = new WP_Quadratum;

?>