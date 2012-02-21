<?php

require_once (WPQUADRATUM_PATH . '/includes/foursquare-helper.php');

function wp_quadratum_add_admin_styles() {
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

function wp_quadratum_add_admin_scripts() {
	global $pagenow;
	
	if ($pagenow == 'options-general.php' &&
			isset ($_GET['page']) &&
			strstr ($_GET['page'], 'wp-quadratum')) {
		wp_enqueue_script ('postbox');
		wp_enqueue_script ('dashboard');
		wp_enqueue_script ('custom-background');
	}
}

function wp_quadratum_admin_init() {
	wp_quadratum_upgrade ();
	
	$wp_quadratum_settings = get_option ('wp_quadratum_settings');
	
	if (empty ($wp_quadratum_settings['oauth_token'])) {
		add_action ('admin_notices', 'wp_quadratum_admin_notice');
	}
}

function wp_quadratum_admin_notice() {
	if (current_user_can ('manage_options')) {
		$content = sprintf (__('You need to grant WP Quadratum access to your Foursquare account to show your checkins; you can go to the <a href="%s">WP Quadratum Settings And Options page</a> to do this now'),
			admin_url ('options-general.php?page=wp-quadratum/includes/wp-quadratum-admin.php'));
		
		echo '<div class="error">' . $content . '</div>';
	}
}

function wp_quadratum_settings_link($links) {
	$settings_link = '<a href="options-general.php?page=wp-quadratum/includes/wp-quadratum-admin.php">'
		. __('Settings')
		. '</a>';
	array_unshift ($links, $settings_link);
	return $links;
}

function wp_quadratum_upgrade() {
	$wp_quadratum_settings  = NULL;
	$upgrade_settings = false;
	$current_plugin_version = NULL;
	
	$wp_quadratum_settings = get_option ('wp_quadratum_settings');
	if (is_array ($wp_quadratum_settings) &&
			!empty ($wp_quadratum_settings['version']) &&
			$wp_quadratum_settings['version'] == WPQUADTRATUM_VERSION) {
		return;
	}
	
	if (!is_array ($wp_quadratum_settings)) {
		wp_quadratum_add_defaults ();
	}
	
	else {
		if (!empty ($wp_quadratum_settings['version'])) {
			$current_plugin_version = $wp_quadratum_settings['version'];
		}
		else {
			$current_plugin_version = '00';
		}
	
		switch ($current_plugin_version) {
			case '00':
			case '10':
			$wp_quadratum_settings['version'] = WPQUADRATUM_VERSION;
			$upgrade_settings = true;
		
			default:
			break;
		}	// end-switch
	
		if ($upgrade_settings) {
			update_option ('wp_quadratum_settings', $wp_quadratum_settings);
		}
	}
}

function wp_quadratum_add_options_subpanel() {
	if (function_exists ('add_options_page')) {
		$page_title = __('WP Quadratum');
		$menu_title = __('WP Quadratum');
		add_options_page ($page_title, $menu_title, 'manage_options', __FILE__,
			'wp_quadratum_settings');
	}
}

function wp_quadratum_settings () {
	$wp_quadratum_settings = wp_quadratum_process_settings ();
	
	$wrapped_content = "";
	$auth_settings = "";
	$auth_title = __('Authentication Settings');
	
	if (empty ($wp_quadratum_settings['oauth_token'])) {
		$auth_title .= __(' (Not Authenticated With Foursquare)');
	}
	
	else {
		$auth_title .= __(' (Successfully Authenticated With Foursquare)');
	}
	
	//$auth_settings .= '<p><strong>' . __('Authentication Status') . '</strong></p>';
	if (empty ($wp_quadratum_settings['oauth_token'])) {
		$auth_settings .= '<div class="wp-quadratum-error">'
			. __('You are not currently authenticated with the Foursquare API.')
			. '</div>';

		$auth_settings .= '<div><p>'
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

		$auth_settings .= '<p><strong>' . __('Foursquare Client ID') . '</strong><br />
			<input type="text" name="wp_quadratum_client_id" id="wp-quadratum-client-id" value="' . $wp_quadratum_settings['client_id'] . '" /><br />
			<small>Your Foursquare API Client ID</small></p>';

		$auth_settings .= '<p><strong>' . __('Foursquare Client Secret') . '</strong><br />
			<input type="text" name="wp_quadratum_client_secret" id="wp-quadratum-client-secret" value="' . $wp_quadratum_settings['client_secret'] . '" /><br />
			<small>Your Foursquare API Client Secret</small></p>';

		$auth_settings .= '<p><strong>'
		. __('Step 3. You should now be authorised and ready to go; click on the Connect button below.')
		. '</strong></p>';

		$auth_settings .= '</p></div>';
		
		$wp_quadratum_settings = wp_quadratum_process_settings ();
		if (!empty ($wp_quadratum_settings['client_id'])) {
			$fh = new FoursquareHelper ($wp_quadratum_settings['client_id'],
				$wp_quadratum_settings['client_secret'],
				plugins_url () . '/' . dirname (plugin_basename (__FILE__)) . '/wp-quadratum-callback.php');
			$auth_settings .= '<p class="submit">'
				. '<a href="' . $fh->authentication_link () . '" class="button-primary">'
				. __('Connect to Foursquare') . '</a>'
				. '</p>';
		}
			
	}
	
	else {
		$auth_settings .= '<div class="wp-quadratum-success">'
			. __('You are currently successfully authenticated with the Foursquare API.')
			. '</div>';
			
	}
	
	if (function_exists ('wp_nonce_field')) {
		$wrapped_content .= wp_nonce_field (
			'wp-quadratum-update-options',
			'_wpnonce',
			true,
			false);
	}
	
	$wrapped_content .= wp_quadratum_postbox ('wp-quadratum-authentication-settings',
		__('Authentication Settings'), $auth_settings);
		
	wp_quadratum_admin_wrap (__('WP Quadratum Settings And Options'), $wrapped_content);
}

function wp_quadratum_option($field) {
	return (isset ($_POST[$field]) ? $_POST[$field] : "");
}

function wp_quadratum_process_settings() {
	$wp_quadratum_settings = get_option ('wp_quadratum_settings');
	
	if (!empty ($_POST['wp_quadratum_option_submitted'])) {
		if (strstr ($_GET['page'], 'wp-quadratum') &&
				check_admin_referer ('wp-quadratum-update-options')) {

			$wp_quadratum_settings['client_id'] = 
				wp_quadratum_option('wp_quadratum_client_id');
			$wp_quadratum_settings['client_secret'] = 
				wp_quadratum_option('wp_quadratum_client_secret');
		
			echo "<div id=\"updatemessage\" class=\"updated fade\"><p>";
			_e('WP Quadratum Settings And Options Updated.');
			echo "</p></div>\n";
			echo "<script type=\"text/javascript\">setTimeout(function(){jQuery('#updatemessage').hide('slow');}, 3000);</script>";	
		
			update_option ('wp_quadratum_settings', $wp_quadratum_settings);
		}
	}
	
	$wp_quadratum_settings = get_option ('wp_quadratum_settings');
	
	return $wp_quadratum_settings;
}

function wp_quadratum_postbox($id, $title, $content) {
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

function wp_quadratum_show_colophon() {
	$content = '<p>'
		. __('WP Quadratum is named after the Latin words quattor, meaning four and quadratum, meaning square.')
		. '</p>';
		
	return wp_quadratum_postbox ('wp-quadratum-colophon', __('Colophon'), $content);
}

function wp_quadratum_admin_wrap($title, $content) {
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
						echo wp_quadratum_show_colophon ();
						//echo wp_biographia_show_acknowledgements ();
                    ?>
                    </div>
                </div>
            </div>
        </form>
    </div>
<?php	
}
?>