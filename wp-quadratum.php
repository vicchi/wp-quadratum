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
require_once (WPQUADRATUM_PATH . '/includes/wp-quadratum-widget.php');

define ('WPNAUTH_PLUGIN_HELPER', WP_PLUGIN_DIR . '/wp-nokia-auth/wp-nokia-auth-helper.php');
define ('WPNAUTH_PLUGIN_PATH', 'wp-nokia-auth/wp-nokia-auth.php');

if (file_exists (WPNAUTH_PLUGIN_HELPER)) {
	include_once (WPNAUTH_PLUGIN_HELPER);
}

include_once (ABSPATH . 'wp-admin/includes/plugin.php');

class WPQuadratum extends WP_PluginBase {
	static $instance;
	
	const OPTIONS = 'wp_quadratum_settings';
	const WPQUADRATUM_VERSION = '10';
	
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

	function plugins_loaded () {
		register_activation_hook (__FILE__, array ($this, 'add_settings'));
		
		$this->hook ('init');
		$this->hook ('widgets_init');
		
		if (is_admin ()) {
			$this->hook ('admin_init');
			$this->hook ('admin_menu');
			$this->hook ('admin_print_scripts');
			$this->hook ('admin_print_styles');
			add_filter ('plugin_action_links_' . plugin_basename (__FILE__),
				array ($this, 'admin_settings_link'));
		}
	}
	
	function init () {
		$lang_dir = basename (dirname (__FILE__)) . DIRECTORY_SEPARATOR . 'lang';
		load_plugin_textdomain ('wp-quadratum', false, $lang_dir);
	}
	
	function widgets_init () {
		return register_widget ('WPQuadratumWidget');
	}
	
	function add_settings () {
		$settings = $this->get_option ();

		if (!is_array ($settings)) {
			$settings = apply_filters ('wp_quadratum_default_settings',
				array (
					"installed" => "on",
					"version" => WPQUADRATUM_VERSION,
					"client_id" => "",
					"client_secret" => "",
					"oauth_token" => ""
					)
				);

			update_option (self::OPTIONS, $settings);
		}
	}
	
	function get_option () {
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

	function set_option ($key, $value) {
		$options = get_options (self::OPTIONS);
		$options[$key] = $value;
		update_option (self::OPTIONS, $options);
	}

	function admin_init () {
		$this->admin_upgrade ();
		$settings = $this->get_option ();

		if (empty ($settings['oauth_token'])) {
			$this->hook ('admin_notices');
		}
	}	

	function admin_notices () {
		if (current_user_can ('manage_options')) {
			$content = sprintf (__('You need to grant WP Quadratum access to your Foursquare account to show your checkins; you can go to the <a href="%s">WP Quadratum Settings And Options page</a> to do this now'),
				admin_url ('options-general.php?page=wp-quadratum/wp-quadratum.php'));

			echo '<div class="error">' . $content . '</div>';
		}
	}
		
	function admin_menu () {
		if (function_exists ('add_options_page')) {
			$page_title = __('WP Quadratum');
			$menu_title = __('WP Quadratum');
			add_options_page ($page_title, $menu_title, 'manage_options', __FILE__,
				array ($this, 'admin_display_settings'));
			
		}
	}

	function admin_print_scripts () {
		global $pagenow;

		if ($pagenow == 'options-general.php' &&
				isset ($_GET['page']) &&
				strstr ($_GET['page'], 'wp-quadratum')) {
			wp_enqueue_script ('postbox');
			wp_enqueue_script ('dashboard');
			wp_enqueue_script ('custom-background');
		}
	}
	
	function admin_print_styles () {
		global $pagenow;

		if ($pagenow == 'options-general.php' &&
				isset ($_GET['page']) &&
				strstr ($_GET['page'], 'wp-quadratum')) {
			wp_enqueue_style ('dashboard');
			wp_enqueue_style ('global');
			wp_enqueue_style ('wp-admin');
			wp_enqueue_style ('farbtastic');
			wp_enqueue_style ('wp-quadratum-admin',
				WPQUADRATUM_URL . 'css/wp-quadratum-admin.css');
		}
	}

	function admin_settings_link ($links) {
		$settings_link = '<a href="options-general.php?page=wp-quadratum/wp-quadratum.php">'
			. __('Settings')
			. '</a>';
		array_unshift ($links, $settings_link);
		return $links;
	}

	function admin_upgrade () {
		$options = null;
		$upgrade_settings = false;
		$current_plugin_version = NULL;

		$options = $this->get_option ();
		if (is_array ($options) &&
				!empty ($options['version']) &&
				$options['version'] == WPQUADTRATUM_VERSION) {
			return;
		}

		if (!is_array ($options)) {
			$this->add_settings ();
		}

		else {
			if (!empty ($options['version'])) {
				$current_plugin_version = $options['version'];
			}
			else {
				$current_plugin_version = '00';
			}

			switch ($current_plugin_version) {
				case '00':
				case '10':
					$options['version'] = WPQUADRATUM_VERSION;
					$upgrade_settings = true;

				default:
				break;
			}	// end-switch

			if ($upgrade_settings) {
				update_option (self::OPTIONS, $options);
			}
		}
	}
	
	function admin_display_settings () {
		$options = $this->admin_save_settings ();
		
//		$auth_plugin_installed = file_exists (WPNAUTH_PLUGIN_HELPER);
//		$auth_plugin_active = is_plugin_active (WPNAUTH_PLUGIN_PATH);
		$auth_plugin_installed = self::is_wpna_installed ();
		$auth_plugin_active = self::is_wpna_active ();
		
		$wrapped_content = array ();
		$foursquare_settings = array ();
		$foursquare_title = __('Foursquare OAuth Settings');
		$nokia_settings = array ();
		$nokia_title = __('Nokia Location API Settings');

		if (empty ($options['oauth_token'])) {
			$foursquare_title .= __(' (Not Authenticated)');
		}

		else {
			$foursquare_title .= __(' (Successfully Authenticated)');
		}

		if (empty ($options['oauth_token'])) {
			$foursquare_settings[] = '<div class="wp-quadratum-error">'
				. __('You are not currently authenticated with the Foursquare API.')
				. '</div>';

			$foursquare_settings[] = '<div><p>'
				. __('To display your Foursquare checkins, WP Quadratum needs to be authorised to access your Foursquare account information; this is a simple, safe and secure 3 step process. QP Quadratum never sees your account login information and cannot store any personally identifiable information.')
				. '<p><strong>'
				. sprintf (__('Step 1. Register this WordPress site as a Foursquare application on the <a target="_blank" href="%s">Foursquare OAuth Consumer Registration</a> page'), 'https://foursquare.com/oauth/register')
				. '</strong></p><p>'
				. __('If you\'re not currently logged into your Foursquare account, you\'ll need to login with the Foursquare account whose checkins you want WP Quadratum to display.')
				. '<ol>'
				. '<li>' . __('The <strong>Application Name</strong> is a label you want to use to identify this connection to your Foursquare account') . '</li>'
				. '<li>' . sprintf (__('The <strong>Application Web Site</strong> is the URL of this Wordpress site, which is <strong>%s</strong>'), get_bloginfo ('url')) . '</li>'
				. '<li>' . sprintf (__('The <strong>Callback URL</strong> should be set to <strong>%s</strong>'), plugins_url() . '/wp-quadratum/includes/wp-quadratum-callback.php') . '</li>'
				. '</ol>'
				. __('Once you have successfully registered your site, you\'ll be provided with two <em>keys</em>, the <em>client id</em> and the <em>client secret</em>')
				. '</p>'
				. '<p><strong>'
				. __('Step 2. Copy and paste the supplied Client ID and Client Secret below')
				. '</strong></p>';

			$foursquare_settings[] = '<p><strong>' . __('Foursquare Client ID') . '</strong><br />
				<input type="text" name="wp_quadratum_client_id" id="wp-quadratum-client-id" value="' . $options['client_id'] . '" /><br />
				<small>Your Foursquare API Client ID</small></p>';

			$foursquare_settings[] = '<p><strong>' . __('Foursquare Client Secret') . '</strong><br />
				<input type="text" name="wp_quadratum_client_secret" id="wp-quadratum-client-secret" value="' . $options['client_secret'] . '" /><br />
				<small>Your Foursquare API Client Secret</small></p>';

			$foursquare_settings[] = '<p><strong>'
			. __('Step 3. You should now be authorised and ready to go; click on the Connect button below.')
			. '</strong></p>';

			$foursquare_settings[] = '</p></div>';

			if (!empty ($options['client_id'])) {
				$fh = new FoursquareHelper ($options['client_id'],
					$options['client_secret'],
					plugins_url () . '/' . dirname (plugin_basename (__FILE__)) . '/includes/wp-quadratum-callback.php');
				$foursquare_settings[] = '<p class="submit">'
					. '<a href="' . $fh->authentication_link () . '" class="button-primary">'
					. __('Connect to Foursquare') . '</a>'
					. '</p>';
			}

		}

		else {
			$foursquare_settings[] = '<div class="wp-quadratum-success">'
				. __('You are currently successfully authenticated with the Foursquare API.')
				. '</div>';

		}

		if ($auth_plugin_installed) {
			if ($auth_plugin_active) {
				$helper = new WPNokiaAuthHelper ();
				
				$nokia_settings[] = '<div class="wp-quadratum-success">'
					. __('WP Nokia Auth is installed and active')
					. '</div>';
				$nokia_settings[] = '<p><strong>' . __('App ID') . '</strong></p>
				<input type="text" size="30" disabled value="' . $helper->get_id () . '"><br />';
				$nokia_settings[] = '<p><strong>' . __('App Token') . '</strong></p>
					<input type="text" size="30" disabled value="' . $helper->get_token () . '"><br />';
				$nokia_settings[] = '<p><strong>' . __('App Secret') . '</strong></p>
					<input type="text" size="30" disabled value="' . $helper->get_secret () . '"><br />';
			}
			
			else {
				$nokia_settings[] = '<div class="wp-quadratum-warning">'
					. __('WP Nokia Auth is installed but not currently active')
					. '</div>';
				
			}
		}

		else {
			$nokia_settings[] = '<p>'
				. sprintf (__('You can use the <a href="%1$s">WP Nokia Auth plugin</a> to manage your Nokia Location Platform API credentials. Or you can obtain Nokia Location API credentials from the <a href="%2$s">Nokia API Registration</a> site.'), 'http://wordpress.org/extend/plugins/wp-nokia-auth/', 'http://api.developer.nokia.com/')
				. '</p>';
			$nokia_settings[] = '<p><strong>' . __('Application ID') . '</strong><br />
				<input type="text" name="wp_quadratum_app_id" id="wp_quadratum_app_id" value="' . $options['app_id'] . '" size="35" /><br />
				<small>' . __('Enter your registered Nokia Location API App ID') . '</small></p>';

			$nokia_settings[] = '<p><strong>' . __('Application Token') . '</strong><br />
				<input type="text" name="wp_quadratum_app_token" id="wp_quadratum_app_token" value="' . $options['app_token'] . '" size="35" /><br />
				<small>' . __('Enter your registered Nokia Location API App Token') . '</small></p>';

			$nokia_settings[] = '<p><strong>' . __('Application Secret') . '</strong><br />
				<input type="text" name="wp_quadratum_app_secret" id="wp_quadratum_app_secret" value="' . $options['app_secret'] . '" size="35" /><br />
				<small>' . __('Enter your registered Nokia Location API App Secret') . '</small></p>';

		}

		if (function_exists ('wp_nonce_field')) {
			$wrapped_content[] = wp_nonce_field (
				'wp-quadratum-update-options',
				'_wpnonce',
				true,
				false);
		}

		$wrapped_content[] = $this->admin_postbox ('wp-quadratum-foursquare-settings',
			$foursquare_title, implode('', $foursquare_settings));

		$wrapped_content[] = $this->admin_postbox ('wp-quadratum-nokia-settings',
			$nokia_title, implode ('', $nokia_settings));
			
		$this->admin_wrap (__('WP Quadratum Settings And Options'), implode ('', $wrapped_content));
	}

	function admin_option ($field) {
		return (isset ($_POST[$field]) ? $_POST[$field] : "");
	}

	function admin_save_settings () {
		$options = $this->get_option ();

		if (!empty ($_POST['wp_quadratum_option_submitted'])) {
			if (strstr ($_GET['page'], 'wp-quadratum') &&
					check_admin_referer ('wp-quadratum-update-options')) {
				$options['client_id'] = $this->admin_option('wp_quadratum_client_id');
				$options['client_secret'] = $this->admin_option('wp_quadratum_client_secret');

				$options['app_id'] = html_entity_decode ($this->admin_option ('wp_quadratum_app_id'));
				$options['app_token'] = html_entity_decode ($this->admin_option ('wp_quadratum_app_token'));
				$options['app_secret'] = html_entity_decode ($this->admin_option ('wp_quadratum_app_secret'));

				echo "<div id=\"updatemessage\" class=\"updated fade\"><p>";
				_e('WP Quadratum Settings And Options Updated.');
				echo "</p></div>\n";
				echo "<script type=\"text/javascript\">setTimeout(function(){jQuery('#updatemessage').hide('slow');}, 3000);</script>";	

				update_option (self::OPTIONS, $options);
			}
		}

		$options = $this->get_option ();
		return $options;
	}
	
	function admin_postbox ($id, $title, $content) {
		$handle_title = __('Click to toggle');

		$postbox_wrap = '<div id="' . $id . '" class="postbox">';
		$postbox_wrap .= '<div class="handlediv" title="'
			. $handle_title
			. '"><br /></div>';
		$postbox_wrap .= '<h3 class="hndle"><span>' . $title . '</span></h3>';
		$postbox_wrap .= '<div class="inside">' . $content . '</div>';
		$postbox_wrap .= '</div>';

		return $postbox_wrap;
	}

	function admin_show_colophon() {
		$content = '<p>'
			. __('WP Quadratum is named after the Latin words quattor, meaning four and quadratum, meaning square.')
			. '</p>';

		return $this->admin_postbox ('wp-quadratum-colophon', __('Colophon'), $content);
	}

	function admin_wrap ($title, $content) {
	?>
	    <div class="wrap">
	        <h2><?php echo $title; ?></h2>
	        <form method="post" action="">
	            <div class="postbox-container wp-quadratum-postbox-settings">
	                <div class="metabox-holder">	
	                    <div class="meta-box-sortables">
	                    <?php
	                        echo $content;
	                    ?>
	                    <p class="submit"> 
	                        <input type="submit" name="wp_quadratum_option_submitted" class="button-primary" value="<?php _e('Save Changes')?>" /> 
	                    </p> 
	                    <br /><br />
	                    </div>
	                  </div>
	                </div>
	                <div class="postbox-container wp-quadratum-postbox-sidebar">
	                  <div class="metabox-holder">	
	                    <div class="meta-box-sortables">
	                    <?php
							//echo wp_biographia_show_help_and_support ();
							echo $this->admin_show_colophon ();
							//echo wp_biographia_show_acknowledgements ();
	                    ?>
	                    </div>
	                </div>
	            </div>
	        </form>
	    </div>
	<?php	
	}
}	// end-class WPQuadratum

$__wp_quadratum_instance = new WPQuadratum;

?>