<?php

if (defined('WP_UNINSTALL_PLUGIN')) {
	delete_option('wp_quadratum_settings');
	delete_option('widget_wp_quadratumwidget');
}

else {
	exit();
}

?>