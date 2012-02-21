<?php
/*
Plugin Name: WP Quadratum
Plugin URI: http://www.vicchi.org/codeage/wp-quadratum/
Description: A WordPress plugin to display your last Foursquare checkin as a widget, fully authenticated via OAuth 2.0.
Version: 1.0
Author: Gary Gale
Author URI: http://www.garygale.com/
License: GPL2
Text Domain: wp-quadratum
*/

define ('WPQUADRATUM_VERSION', '10');
define ('WPQUADRATUM_URL', plugin_dir_url (__FILE__));
define ('WPQUADRATUM_PATH', plugin_dir_path (__FILE__));

require_once (WPQUADRATUM_PATH . '/includes/wp-quadratum-admin.php');
require_once (WPQUADRATUM_PATH . '/includes/wp-quadratum-widget.php');

function wp_quadratum_add_defaults() {
	$wp_quadratum_settings = NULL;
	
	$wp_quadratum_settings = get_option ('wp_quadratum_settings');
	if (!is_array ($wp_quadratum_settings)) {
		$wp_quadratum_settings = array (
			"installed" => "on",
			"version" => WPQUADRATUM_VERSION,
			"client_id" => "",
			"client_secret" => "",
			"oauth_token" => ""
			);
			
		update_option ('wp_quadratum_settings', $wp_quadratum_settings);
	}
}

function wp_quadratum_widgets_init() {
	return register_widget ('WPQuadratumWidget');
}

function wp_quadratum_init() {
	$lang_dir = basename (dirname (__FILE__)) . DIRECTORY_SEPARATOR . 'lang';
	load_plugin_textdomain ('wp-quadratum', false, $lang_dir);
}

register_activation_hook (__FILE__, 'wp_quadratum_add_defaults');

add_action ('admin_menu', 'wp_quadratum_add_options_subpanel');
add_action ('init', 'wp_quadratum_init');
add_action ('admin_init', 'wp_quadratum_admin_init');
add_action ('admin_print_scripts', 'wp_quadratum_add_admin_scripts');
add_action ('admin_print_styles', 'wp_quadratum_add_admin_styles');
add_action ('widgets_init', 'wp_quadratum_widgets_init');

add_filter ('plugin_action_links_' . plugin_basename (__FILE__), 'wp_quadratum_settings_link');

?>