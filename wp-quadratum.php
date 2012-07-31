<?php
/*
Plugin Name: WP Quadratum
Plugin URI: http://www.vicchi.org/codeage/wp-quadratum/
Description: A WordPress plugin to display your last Foursquare checkin as a widget, fully authenticated via OAuth 2.0.
Version: 1.1
Author: Gary Gale
Author URI: http://www.garygale.com/
License: GPL2
Text Domain: wp-quadratum
*/

define ('WPQUADRATUM_URL', plugin_dir_url (__FILE__));
define ('WPQUADRATUM_PATH', plugin_dir_path (__FILE__));

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

require_once (WPQUADRATUM_PATH . '/wp-plugin-base/wp-plugin-base.php');
require_once (WPQUADRATUM_PATH . '/wp-mxn-helper/wp-mxn-helper.php');
require_once (WPQUADRATUM_PATH . '/includes/wp-quadratum-widget.php');

define ('WPNAUTH_PLUGIN_HELPER', WP_PLUGIN_DIR . '/wp-nokia-auth/wp-nokia-auth-helper.php');
define ('WPNAUTH_PLUGIN_PATH', 'wp-nokia-auth/wp-nokia-auth.php');

if (file_exists (WPNAUTH_PLUGIN_HELPER)) {
	include_once (WPNAUTH_PLUGIN_HELPER);
}

include_once (ABSPATH . 'wp-admin/includes/plugin.php');

class WP_Quadratum extends WP_PluginBase {
	static $instance;
	
	const OPTIONS = 'wp_quadratum_settings';
	const VERSION = '110';
	const DISPLAY_VERSION = 'v1.1.0';
	
	function __construct () {
		self::$instance = $this;
		
		$this->hook ('plugins_loaded');
	}

	static function is_wpna_installed () {
		return file_exists (WPNAUTH_PLUGIN_HELPER);
	}

	static function is_wpna_active () {
		return is_plugin_active (WPNAUTH_PLUGIN_PATH);
	}

	static function make_redirect_url () {
		return plugins_url ()
			. '/'
			. dirname (plugin_basename (__FILE__))
			. '/includes/wp-quadratum-callback.php';
	}

	static function make_settings_link () {
		return 'plugin_action_links_' . plugin_basename (__FILE__);
	}
	
	function plugins_loaded () {
		error_log ('plugins_loaded');
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
	
	function init () {
		$lang_dir = basename (dirname (__FILE__)) . DIRECTORY_SEPARATOR . 'lang';
		load_plugin_textdomain ('wp-quadratum', false, $lang_dir);
	}
	
	function widgets_init () {
		return register_widget ('WP_QuadratumWidget');
	}
	
	static function add_settings () {
		error_log ('add_settings');
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

	static function set_option ($key, $value) {
		$options = get_options (self::OPTIONS);
		$options[$key] = $value;
		update_option (self::OPTIONS, $options);
	}

	static function get_foursquare_checkins () {
		$client_id = WP_Quadratum::get_option ('client_id');
		$client_secret = WP_Quadratum::get_option ('client_secret');
		$oauth_token = WP_Quadratum::get_option ('oauth_token');
		$redirect_url = WP_Quadratum::make_redirect_url ();
		$endpoint = "users/self/checkins";
		$params = array ('limit' => 1);

		$fsq = new FoursquareHelper ($client_id, $client_secret, $redirect_url);
		$fsq->set_access_token ($oauth_token);
		$rsp = $fsq->get_private ($endpoint, $params);
		$json = json_decode ($rsp);
		return $json;
	}

}	// end-class WP_Quadratum

$__wp_quadratum_instance = new WP_Quadratum;

?>