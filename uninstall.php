<?php

if (defined ('WP_UNINSTALL_PLUGIN')) {
	delete_option ('wp_quadratum_settings');
}

else {
	exit ();
}

?>