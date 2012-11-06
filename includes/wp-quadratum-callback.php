<?php

/**
 * Oauth callback handler code ... yes, this is fugly, yes, I know
 */

if (array_key_exists ('code', $_GET) && !function_exists ('site_url')) {
	require_once ('../../../../wp-config.php');

	$wp_quadratum_settings = get_option ('wp_quadratum_settings');
	
	$client_id = $wp_quadratum_settings['client_id'];
	$client_secret = $wp_quadratum_settings['client_secret'];
	$redirect_url = plugins_url ()
		. '/'
		. dirname (plugin_basename (__FILE__))
		. '/wp-quadratum-callback.php';
	
	$fh = new FoursquareHelper_v1_0 ($client_id, $client_secret, $redirect_url);
	
	$token = $fh->get_token ($_GET['code']);
	
	$wp_quadratum_settings['oauth_token'] = $token;

	update_option ('wp_quadratum_settings', $wp_quadratum_settings);

	$redirect_url = site_url ()
		. '/wp-admin/options-general.php?page=wp-quadratum/includes/wp-quadratum-admin.php';

	wp_redirect ($redirect_url);
}

?>