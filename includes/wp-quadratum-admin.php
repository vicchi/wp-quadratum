<?php

class WP_QuadratumAdmin extends WP_PluginBase {
	private $mxn;
	
	static $tab_names;
	
	function __construct () {
		$this->mxn = new WP_MXNHelper;
		
		self::$tab_names = array (
			'foursquare' => "Foursquare",
			'maps' => "Maps",
			'defaults' => "Defaults",
			'colophon' => "Colophon"
			);
		
		$this->hook ('admin_init');
		$this->hook ('admin_menu');
		$this->hook ('admin_print_scripts');
		$this->hook ('admin_print_styles');
		$this->hook (WP_Quadratum::make_settings_link (), 'admin_settings_link');
	}

	function admin_init () {
		error_log ('admin_init');
		$this->admin_upgrade ();
		$options = WP_Quadratum::get_option ();

		if (empty ($options['oauth_token'])) {
			$this->hook ('admin_notices');
		}
	}	

	function admin_notices () {
		if (current_user_can ('manage_options')) {
			$content = sprintf (__('You need to grant WP Quadratum access to your Foursquare account to show your checkins; you can go to the <a href="%s">WP Quadratum Settings And Options page</a> to do this now'),
				admin_url ('options-general.php?page=wp-quadratum/includes/wp-quadratum-admin.php'));

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
			//wp_enqueue_script ('custom-background');
			wp_enqueue_script ('jquery');
			$deps = array ('jquery');
			//wp_enqueue_script ('wp-quadratum-admin-script', WPQUADRATUM_URL . 'js/wp-quadratum-admin.min.js', $deps);
			wp_enqueue_script ('wp-quadratum-admin-script', WPQUADRATUM_URL . 'js/wp-quadratum-admin.js', $deps);
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
			wp_enqueue_style ('wp-quadratum-admin',
				WPQUADRATUM_URL . 'css/wp-quadratum-admin.css');
		}
	}

	function admin_settings_link ($links) {
		$settings_link = '<a href="options-general.php?page=wp-quadratum/includes/wp-quadratum-admin.php">'
			. __('Settings')
			. '</a>';
		array_unshift ($links, $settings_link);
		return $links;
	}

	function admin_upgrade_option (&$options, $key, $value) {
		if (!isset ($options[$key])) {
			$options[$key] = $value;
		}
	}

	function admin_upgrade () {
		error_log ('admin_upgrade');
		$options = null;
		$upgrade_settings = false;
		$current_plugin_version = NULL;

		$options = WP_Quadratum::get_option ();
		if (is_array ($options) &&
				!empty ($options['version']) &&
				$options['version'] == WP_Quadratum::VERSION) {
			return;
		}

		if (!is_array ($options)) {
			error_log ('calling WP_Quadratum::add_settings');
			WP_Quadratum::add_settings ();
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
				case '101':
				case '102':
				case '110':
					$this->admin_upgrade_option ($options, 'provider', 'nokia');
					
					if (isset ($options['app_id'])) {
						$this->admin_upgrade_option ($options, 'nokia_app_id', $options['app_id']);
						unset ($options['app_id']);
					}
					if (isset ($options['app_token'])) {
						$this->admin_upgrade_option ($options, 'nokia_app_token', $options['app_token']);
						unset ($options['app_token']);
					}

					$this->admin_upgrade_option ($options, 'google_key', '');
					$this->admin_upgrade_option ($options, 'google_sensor', 'false');
					$this->admin_upgrade_option ($options, 'cloudmade_key', '');
				
					$options['version'] = WP_Quadratum::VERSION;
					$upgrade_settings = true;

				default:
					break;
			}	// end-switch

			if ($upgrade_settings) {
				update_option (WP_Quadratum::OPTIONS, $options);
			}
		}
	}
	
	function admin_display_settings () {
		$options = $this->admin_save_settings ();
		
		$auth_plugin_installed = WP_Quadratum::is_wpna_installed ();
		$auth_plugin_active = WP_Quadratum::is_wpna_active ();
		
		$wrapped_content = array ();
		$foursquare_settings = array ();
		$foursquare_title = __('Foursquare OAuth Settings');
		$nokia_settings = array ();
		$nokia_title = __('Nokia Location API Settings');

		$maps_settings = array ();
		$googlev3_settings = array ();
		//$leaflet_settings = array ();
		$cloudmade_settings = array ();
		$openlayers_settings = array ();
		//$openmq_settings = array ();
		
		$tab = $this->admin_validate_tab ();
		$providers = WP_MXNHelper::get_supported_providers ();
		
		switch ($tab) {
			case 'maps':
				/****************************************************************************
 	 	 	 	 * Maps API Selection & Authentication tab content
 	 	 	 	 */
				$maps_settings[] = '<p><em>' . __('This tab allows you to choose which Mapstraction mapping API will be used to show your Foursquare checkins. Note that not all supported Mapstraction APIs are shown here; support for more mapping APIs will be added in a future release.', 'wp-quadratum') . '</em></p>';

				$maps_settings[] = '<select name="wp_quadratum_map_provider" id="wp-quadratum-map-provider">';
				foreach ($providers as $provider => $chars) {
					$maps_settings[] = '<option value="' . $provider . '"' . selected ($options['provider'], $provider, false) . '>' . $chars['description'] . '</option>';
				}	// end-foreach
				$maps_settings[] = '</select>';
				if ($auth_plugin_installed) {
					if ($auth_plugin_active) {
						$helper = new WPNokiaAuthHelper ();

						$nokia_settings[] = '<div class="wp-quadratum-success">'
							. __('WP Nokia Auth is installed and active')
							. '</div>';
						$nokia_settings[] = '<p><strong>' . __('App ID') . '</strong></p>
						<input type="text" size="30" disabled value="' . $helper->get_id () . '"><br />';
						$nokia_settings[] = '<p><strong>' . __('Token / App Code') . '</strong></p>
							<input type="text" size="30" disabled value="' . $helper->get_token () . '"><br />';
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
					$nokia_settings[] = '<p><strong>' . __('App ID') . '</strong><br />
						<input type="text" name="wp_quadratum_nokia_app_id" id="wp_quadratum_nokia_app_id" value="' . $options['nokia_app_id'] . '" size="35" /><br />
						<small>' . __('Enter your registered Nokia Location API App ID') . '</small></p>';

					$nokia_settings[] = '<p><strong>' . __('Token / App Code') . '</strong><br />
						<input type="text" name="wp_quadratum_nokia_app_token" id="wp_quadratum_nokia_app_token" value="' . $options['nokia_app_token'] . '" size="35" /><br />
						<small>' . __('Enter your registered Nokia Location API Token / App Code') . '</small></p>';
				}
				
				$googlev3_settings[] = '<p><em>'
					. __('You\'ve selected Google Maps. To use Google Maps, you\'ll need an API key; you can get one from the <a href="https://code.google.com/apis/console/" target="_blank">Google Code APIs Console</a>.', 'wp-quadratum')
					. '</em></p>';
				$googlev3_settings[] = '<p><strong>' . __("Google Maps API Key", 'wp-quadratum') . '</strong><br />
					<input type="text" name="wp_quadratum_google_key" id="wp-quadratum-google-key" size="40" value="'
					. $options["google_key"]
					. '" /><br />
					<small>' . __('Enter your Google Maps API key', 'wp-quadratum') . '</small></p>';

				//$leaflet_settings[] = '<p><em>'
				//	. __('You\'ve selected Leaflet Maps. That\'s all there is. No settings, no API key, no options.', 'wp-quadratum')
				//	. '</em></p>';

				$cloudmade_settings[] = '<p><em>'
					. __('You\'ve selected CloudMade Maps. To use CloudMade Maps, you\'ll need an API key; you can get one from the <a href="http://developers.cloudmade.com/projects" target="_blank">CloudMade Developer Zone</a>.', 'wp-quadratum')
					. '</em></p>';
				$cloudmade_settings[] = '<p><strong>' . __("CloudMade API Key", 'wp-quadratum') . '</strong><br />
					<input type="text" name="wp_quadratum_cloudmade_key" id="wp-quadratum-cloudmade-key" size="40" value="'
					. $options["cloudmade_key"]
					. '" /><br />
					<small>' . __('Enter your CloudMade API key', 'wp-quadratum') . '</small></p>';

				$openlayers_settings[] = '<p><em>'
					. __('You\'ve selected OpenLayers Maps. That\'s all there is. No settings, no API key, no options.', 'wp-quadratum')
					. '</em></p>';
					
				//$openmq_settings[] = '<p><em>'
				//	. __('You\'ve selected MapQuest Open Maps. That\'s all there is. No settings, no API key, no options.', 'wp-quadratum')
				//	. '</em></p>';
					
				break;
				
			case 'defaults':
				/****************************************************************************
 	 	 	 	 * Defaults tab content
 	 	 	 	 */

				$defaults_settings[] = '<p><em>' . __('<strong>Here Be Dragons</strong>. Please <strong>read</strong> the warning below before doing anything with this tab. The options in this tab with reset WP Quadratum to a just installed state, clearing any configuration settings you may have made.', 'wp-quadratum') . '</em></p>';

				$defaults_settings[] = '<p><strong>' . __('Reset WP Quadratum To Defaults', 'wp-quadratum') . '</strong><br />
					<input type="checkbox" name="wp_quadratum_reset_defaults" />
					<small>' . __('Reset all WP Quadratum settings and options to their default values.', 'wp-quadratum') . '</small></p>';
				$defaults_settings[] = '<p>';
				$defaults_settings[] = sprintf (__('<strong>WARNING!</strong> Checking <strong><em>%s</em></strong> and clicking on <strong><em>%s</em></strong> will erase <strong><em>all</em></strong> the current WP Quadratum settings and options and will restore WP Quadratum to a <em>just installed</em> state. This is the equivalent to deactivating, uninstalling and reinstalling the plugin. Only proceed if this is what you intend to do. This action is final and irreversable.', 'wp-quadratum'), __('Reset WP Quadratum To Defaults', 'wp-quadratum'), __('Save Changes', 'wp-quadratum'));
				$defaults_settingsp[] = '</p>';
				break;
				
			case 'colophon':
				/****************************************************************************
 	 	 	 	 * Colophon tab content
 	 	 	 	 */
				
				$colophon_settings[] = '<p><em>"When it comes to software, I much prefer free software, because I have very seldom seen a program that has worked well enough for my needs and having sources available can be a life-saver"</em>&nbsp;&hellip;&nbsp;Linus Torvalds</p>';
				$colophon_settings[] = '<p>'
					. __('For the inner nerd in you, the latest version of WP Quadratum was written using <a href="http://macromates.com/">TextMate</a> on a MacBook Pro running OS X 10.7.3 Lion and tested on the same machine running <a href="http://mamp.info/en/index.html">MAMP</a> (Mac/Apache/MySQL/PHP) before being let loose on the author\'s <a href="http://www.vicchi.org/">blog</a>.')
					. '<p>';
				$colophon_settings[] = '<p>'
					. __('The official home for WP Quadratum is on <a href="http://www.vicchi.org/codeage/wp-quadratum/">Gary\'s Codeage</a>; it\'s also available from the official <a href="http://wordpress.org/extend/plugins/wp-quadratum/">WordPress plugins repository</a>. If you\'re interested in what lies under the hood, the code is also on <a href="https://github.com/vicchi/wp-quadratum">GitHub</a> to download, fork and otherwise hack around.')
					. '<p>';
				$colophon_settings[] = '<p>'
					. __('WP Quadratum is named after both the Latin words <em>quattor</em>, meaning four and <em>quadratum</em>, meaning square.')
					. '</p>';
				break;

			case 'foursquare':
			default:
				/****************************************************************************
	 	 	 	 * Foursquare Authentication tab content
	 	 	 	 */

				if (empty ($options['oauth_token'])) {
					$foursquare_title .= __(' (Not Authenticated)');
				}

				else {
					$foursquare_title .= __(' (Successfully Authenticated)');
				}

				$foursquare_settings[] = '<p><em>' . __('This tab allows you to authenticate with Foursquare to allow WP Quadratum to display your checkins.', 'wp-quadratum') . '</em></p>';

				if (empty ($options['oauth_token'])) {
					$foursquare_settings[] = '<div class="wp-quadratum-error">'
						. __('You are not currently authenticated with the Foursquare API.')
						. '</div>';

					$foursquare_settings[] = '<div><p>'
						. __('To display your Foursquare checkins, WP Quadratum needs to be authorised to access your Foursquare account information; this is a simple, safe and secure 3 step process. QP Quadratum never sees your account login information and cannot store any personally identifiable information.')
						. '</p>'
						. '<p><strong>'
						. sprintf (__('Step 1. Register this WordPress site as a Foursquare application on the <a target="_blank" href="%s">Foursquare OAuth Consumer Registration</a> page'), 'https://foursquare.com/oauth/register')
						. '</strong></p><p>'
						. __('If you\'re not currently logged into your Foursquare account, you\'ll need to login with the Foursquare account whose checkins you want WP Quadratum to display.')
						. '<ol>'
						. '<li>' . __('The <strong>Application Name</strong> is a label you want to use to identify this connection to your Foursquare account') . '</li>'
						. '<li>' . sprintf (__('The <strong>Application Web Site</strong> is the URL of this Wordpress site, which is <strong>%s</strong>'), home_url ()) . '</li>'
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

					if (!empty ($options['client_id'])) {
						$foursquare_settings[] = '<p><strong>'
						. __('Step 3. You should now be authorised and ready to go; click on the Connect button below.')
						. '</strong></p>';

						$client_id = $options['client_id'];
						$client_secret = $options['client_secret'];
						$redirect_url = WP_Quadratum::make_redirect_url ();
						$fh = new FoursquareHelper ($client_id, $client_secret, $redirect_url);
						$foursquare_settings[] = '<p class="submit">'
							. '<a href="' . $fh->authentication_link () . '" class="button-primary">'
							. __('Connect to Foursquare') . '</a>'
							. '</p>';
					}

					$foursquare_settings[] = '</div>';
				}

				else {
					$foursquare_settings[] = '<div class="wp-quadratum-success">'
						. __('You are currently successfully authenticated with the Foursquare API.')
						. '</div>';
				}
				break;

		}	// end-switch ($tab);
		
		/****************************************************************************
 	 	 * Put it all together ...
 	 	 */

		if (function_exists ('wp_nonce_field')) {
			$wrapped_content[] = wp_nonce_field (
				'wp-quadratum-update-options',
				'_wpnonce',
				true,
				false);
		}

		$tab = $this->admin_validate_tab ();
		switch ($tab) {
			case 'maps':
				$wrapped_content[] = $this->admin_postbox ('wp-quadratum-maps-settings',
					'Maps Provider', implode ('', $maps_settings));

				$current_provider = $options['provider'];
				foreach ($providers as $provider => $chars) {
					$id = 'wp-quadratum-' . $provider . '-settings';
					$title = $chars['description'] . ' Settings';
					$hidden = ($current_provider != $provider);
					$block = $provider . '_settings';
					$wrapped_content[] = $this->admin_postbox ($id, $title, implode ('', $$block), $hidden);
				}	// end-foreach
				break;
				
			case 'defaults':
				$wrapped_content[] = $this->admin_postbox ('wp-quadratum-defaults-settings',
					'Defaults', implode ('', $defaults_settings));
				break;
				
			case 'colophon':
				$wrapped_content[] = $this->admin_postbox ('wp-quadratum-colophon-settings',
					'Colophon', implode ('', $colophon_settings));
				break;
				
			case 'foursquare':
			default:
				$wrapped_content[] = $this->admin_postbox ('wp-quadratum-foursquare-settings',
					$foursquare_title, implode('', $foursquare_settings));
				break;
		}	// end-switch ($tab)
		
		$this->admin_wrap ($tab,
			sprintf (__('WP Quadratum %s - Settings And Options'), WP_Quadratum::DISPLAY_VERSION),
				implode ('', $wrapped_content));
	}

	function admin_option ($field) {
		return (isset ($_POST[$field]) ? $_POST[$field] : "");
	}

	function admin_save_settings () {
		$options = WP_Quadratum::get_option ();

		if (!empty ($_POST['wp_quadratum_option_submitted'])) {
			if (strstr ($_GET['page'], 'wp-quadratum') &&
					check_admin_referer ('wp-quadratum-update-options')) {
				$tab = $this->admin_validate_tab ();
				$update_options = true;
				$reset_options = false;
				$update_msg = self::$tab_names[$tab];
				$action_msg = __('Updated', 'wp-quadratum');
				
				switch ($tab) {
					case 'foursquare':
						$options['client_id'] = $this->admin_option('wp_quadratum_client_id');
						$options['client_secret'] = $this->admin_option('wp_quadratum_client_secret');
						break;
						
					case 'maps':
						$options['provider'] = $this->admin_option ('wp_quadratum_map_provider');
						$options['nokia_app_id'] = html_entity_decode ($this->admin_option ('wp_quadratum_nokia_app_id'));
						$options['nokia_app_token'] = html_entity_decode ($this->admin_option ('wp_quadratum_nokia_app_token'));
						$options['google_key'] = html_entity_decode ($this->admin_option ('wp_quadratum_google_key'));
						$options['cloudmade_key'] = html_entity_decode ($this->admin_option ('wp_quadratum_cloudmade_key'));
						break;
						
					case 'defaults':
						$update_options = false;
						if (isset ($_POST['wp_quadratum_reset_defaults']) &&
								$_POST['wp_quadratum_reset_defaults'] === 'on') {
							$reset_options = true;
							$this->admin_reset_plugin ();
							$update_msg = __('All', 'wp-quadratum');
							$update_action = __('Reset To Default Values', 'wp-quadratum');
						}
						break;
						
					case 'colophon':
					default:
						$update_options = false;
						break;
				}	// end-switch ($tab)
				
				if ($update_options) {
					update_option (WP_Quadratum::OPTIONS, $options);
				}

				if ($update_options || $reset_options) {
					echo "<div id=\"updatemessage\" class=\"updated fade\"><p>";
					echo sprintf (__('%s Settings And Options %s', 'wp-quadratum'),
						$update_msg, $action_msg);
					echo "</p></div>\n";
					echo "<script 	type=\"text/javascript\">setTimeout(function(){jQuery('#updatemessage').hide('slow');}, 3000);</script>";	
				}
			}
		}

		$options = WP_Quadratum::get_option ();
		return $options;
	}
	
	function admin_postbox ($id, $title, $content, $hidden=false) {
		$handle_title = __('Click to toggle');

		$postbox_wrap = '<div id="' . $id . '" class="postbox"';
		if ($hidden) {
			$postbox_wrap .= ' style="display:none;"';
		}
		$postbox_wrap .= '>';
		$postbox_wrap .= '<div class="handlediv" title="'
			. $handle_title
			. '"><br /></div>';
		$postbox_wrap .= '<h3 class="hndle"><span>' . $title . '</span></h3>';
		$postbox_wrap .= '<div class="inside">' . $content . '</div>';
		$postbox_wrap .= '</div>';

		return $postbox_wrap;
	}

	function admin_wrap ($tab, $title, $content) {
		$action = admin_url ('options-general.php');
		$action .= '?page=wp-quadratum/includes/wp-quadratum-admin.php&tab=' . $tab;
	?>
	    <div class="wrap">
	        <h2><?php echo $title; ?></h2>
			<?php echo $this->admin_tabs ($tab); ?>
	        <form method="post" action="">
	            <div class="postbox-container wp-quadratum-postbox-settings">
	                <div class="metabox-holder">	
	                    <div class="meta-box-sortables">
	                    <?php
	                        echo $content;
							echo $this->admin_submit ($tab);
	                    ?>
	                    <br /><br />
	                    </div>
	                  </div>
	                </div>
	                <div class="postbox-container wp-quadratum-postbox-sidebar">
	                  <div class="metabox-holder">	
	                    <div class="meta-box-sortables">
	                    <?php
							echo $this->admin_help_and_support ();
							echo $this->admin_show_colophon ();
	                    ?>
	                    </div>
	                </div>
	            </div>
	        </form>
	    </div>
	<?php	
	}

	function admin_submit ($tab) {
		$content = array ();
		
		switch ($tab) {
			case 'foursquare':
			case 'maps':
			case 'defaults':
            	$content[] = '<p class="submit">';
				$content[] = '<input type="submit" name="wp_quadratum_option_submitted" class="button-primary" value="';
				$content[] = sprintf (__('Save %s Settings', 'wp-quadratum'),
				 	self::$tab_names[$tab]);
				$content[] = '" />';
				$content[] = '</p>';
				return implode ('', $content);
				break;

			case 'colophon':
			default:
				break;
		}	// end-switch ($tab)
	}

	/**
	 * Emits the plugin's help/support side-box for the plugin's admin settings/options page.
	 */

	function admin_help_and_support () {
		$email_address = antispambot ("gary@vicchi.org");

		$content = '<p>'
			. __('For help and support with WP Quadratum, here\'s what you can do:')
			. '<ul>'
			. '<li>'
			. sprintf (__('Firstly ... take a look at <a href="%s">this</a> before firing off a question.'), 'http://www.vicchi.org/2012/03/31/asking-for-wordpress-plugin-help-and-support-without-tears/')
			. '</li>'
			. '<li>'
			. __('Then ... ask a question on the <a href="http://wordpress.org/tags/wp-quadratum?forum_id=10">WordPress support forum</a>; this is by far the best way so that other users can follow the conversation.')
			. '</li>'
			. '<li>'
			. __('Or ... ask me a question on Twitter; I\'m <a href="http://twitter.com/vicchi">@vicchi</a>.')
			. '</li>'
			. '<li>'
			. sprintf (__('Or ... drop me an <a href="mailto:%s">email </a>instead.'), $email_address)
			. '</li>'
			. '</ul>'
			. '</p>'
			. '<p>'
			. __('But ... help and support is a two way street; here\'s what you might want to do:')
			. '<ul>'
			. '<li>'
			. sprintf (__('If you like this plugin and use it on your WordPress site, or if you write about it online, <a href="http://www.vicchi.org/codeage/wp-quadratum/">link to the plugin</a> and drop me an <a href="mailto:%s">email</a> telling me about this.'), $email_address)
			. '</li>'
			. '<li>'
			. __('Rate the plugin on the <a href="http://wordpress.org/extend/plugins/wp-quadratum/">WordPress plugin repository</a>.')
			. '</li>'
			. '<li>'
			. __('WP Quadratum is both free as in speech and free as in beer. No donations are required; <a href="http://www.vicchi.org/codeage/donate/">here\'s why</a>.')
			. '</li>'
			. '</ul>'
			. '</p>';

		return $this->admin_postbox ('wp-quadratum-support', __('Help &amp; Support'), $content);
	}

	/**
	 * Emits the plugin's colophon side-box for the plugin's admin settings/options page.
	 */

	function admin_show_colophon() {
		$content = '<p><em>"When it comes to software, I much prefer free software, because I have very seldom seen a program that has worked well enough for my needs and having sources available can be a life-saver"</em>&nbsp;&hellip;&nbsp;Linus Torvalds</p>';

		$content .= '<p>'
			. __('For the inner nerd in you, the latest version of WP Quadratum was written using <a href="http://macromates.com/">TextMate</a> on a MacBook Pro running OS X 10.7.3 Lion and tested on the same machine running <a href="http://mamp.info/en/index.html">MAMP</a> (Mac/Apache/MySQL/PHP) before being let loose on the author\'s <a href="http://www.vicchi.org/">blog</a>.')
			. '<p>';

		$content .= '<p>'
			. __('The official home for WP Quadratum is on <a href="http://www.vicchi.org/codeage/wp-quadratum/">Gary\'s Codeage</a>; it\'s also available from the official <a href="http://wordpress.org/extend/plugins/wp-quadratum/">WordPress plugins repository</a>. If you\'re interested in what lies under the hood, the code is also on <a href="https://github.com/vicchi/wp-quadratum">GitHub</a> to download, fork and otherwise hack around.')
			. '<p>';

		$content .= '<p>'
			. __('WP Quadratum is named after both the Latin words <em>quattor</em>, meaning four and <em>quadratum</em>, meaning square.')
			. '</p>';

		return $this->admin_postbox ('wp-quadratum-colophon', __('Colophon'), $content);
	}

	function admin_tabs ($current='foursquare') {
		$content = array ();
		
		$content[] = '<div id="icon-tools" class="icon32"><br /></div>';
		$content[] = '<h2 class="nav-tab-wrapper">';
		
		foreach (self::$tab_names as $tab => $name) {
			$class = ($tab == $current) ? ' nav-tab-active' : '';
			$content[] = "<a class='nav-tab$class' href='options-general.php?page=wp-quadratum/includes/wp-quadratum-admin.php&tab=$tab'>$name</a>";
		}	// end-foreach (...)
		
		$content[] = '</h2>';
		
		return implode ('', $content);
	}
	
	/**
	 * Check and validate the tab parameter passed as part of the settings/options URL.
	 */

	function admin_validate_tab () {
		$tab = 'foursquare';
		if (isset ($_GET['tab'])) {
			if (array_key_exists ($_GET['tab'], self::$tab_names)) {
				$tab = $_GET['tab'];
			}
		}

		return $tab;
	}
	
	/**
	 * Reset the plugin's settings/options back to the default values.
	 */
	
	function admin_reset_plugin () {
		delete_option (WP_Quadratum::OPTIONS);
		WP_Quadratum::add_settings ();
	}
	
	
}	// end-class WP_QuadratumAdmin

$__wp_quadratumadmin_instance = new WP_QuadratumAdmin;

?>