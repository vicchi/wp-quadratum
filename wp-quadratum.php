<?php
/*
Plugin Name: WP Quadratum
Plugin URI: http://www.vicchi.org/codeage/wp-quadratum/
Description: A WordPress plugin to display your last Foursquare checkin as a map widget, fully authenticated via OAuth 2.0.
Version: 1.3.0
Author: Gary Gale
Author URI: http://www.garygale.com/
License: GPL2
Text Domain: wp-quadratum
*/

define('WPQUADRATUM_URL', plugin_dir_url (__FILE__));
define('WPQUADRATUM_PATH', plugin_dir_path (__FILE__));
define('WPQUADRATUM_NAME', plugin_basename(__FILE__));
//define ('WPQUADRATUM_DEBUG', true);

/*
 * Determine WordPress directory constants.
 * See http://codex.wordpress.org/Determining_Plugin_and_Content_Directories
 */

if (!defined('WP_CONTENT_URL'))
	define('WP_CONTENT_URL', WP_SITEURL . '/wp-content');
if (!defined('WP_CONTENT_DIR'))
	define('WP_CONTENT_DIR', ABSPATH . 'wp-content');
if (!defined('WP_PLUGIN_URL'))
	define('WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins');
if (!defined('WP_PLUGIN_DIR'))
	define('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins');
if (!defined('WPMU_PLUGIN_URL'))
	define('WPMU_PLUGIN_URL', WP_CONTENT_URL. '/mu-plugins');
if (!defined('WPMU_PLUGIN_DIR'))
	define('WPMU_PLUGIN_DIR', WP_CONTENT_DIR . '/mu-plugins');

define('WPMAPSTRACTION_SRC', WPQUADRATUM_PATH . 'includes/wp-mapstraction/class-wp-mapstraction.php');
define('WPQUADRATUM_SRC', WPQUADRATUM_PATH . 'includes/class-wp-quadratum.php');
define('WPQUADRATUM_WIDGET_SRC', WPQUADRATUM_PATH . 'includes/class-wp-quadratum-widget.php');
define('WPQUADRATUM_ADMIN_SRC', WPQUADRATUM_PATH . 'includes/class-wp-quadratum-admin.php');
define('WPQUADRATUM_FRONTEND_SRC', WPQUADRATUM_PATH . 'includes/class-wp-quadratum-frontend.php');

define('FOURSQUARE_HELPER_SRC', WPQUADRATUM_PATH . 'includes/foursquare-helper/foursquare-helper.php');

include_once(ABSPATH . 'wp-admin/includes/plugin.php');

require_once (FOURSQUARE_HELPER_SRC);

require_once(WPQUADRATUM_PATH . '/includes/wp-plugin-base/wp-plugin-base.php');
require_once(WPMAPSTRACTION_SRC);
require_once(WPQUADRATUM_WIDGET_SRC);
require_once(WPQUADRATUM_SRC);

?>