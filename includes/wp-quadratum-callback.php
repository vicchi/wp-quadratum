<?php

// OAuth callback handler code ...

if (array_key_exists ('code', $_GET) && !function_exists ('get_bloginfo')) {
	require_once ('../../../../wp-config.php');

	$wp_quadratum_settings = get_option ('wp_quadratum_settings');
	
	$client_id = $wp_quadratum_settings['client_id'];
	$client_secret = $wp_quadratum_settings['client_secret'];
	$redirect_url = plugins_url ()
		. '/'
		. dirname (plugin_basename (__FILE__))
		. '/wp-quadratum-callback.php';
	
	$fh = new FoursquareHelper ($client_id, $client_secret, $redirect_url);
	
	$token = $fh->get_token ($_GET['code']);
	
	$wp_quadratum_settings['oauth_token'] = $token;

	update_option ('wp_quadratum_settings', $wp_quadratum_settings);

	$redirect_url = get_bloginfo ('wpurl')
		. '/wp-admin/options-general.php?page=wp-quadratum/wp-quadratum.php';

	wp_redirect ($redirect_url);
}

?>