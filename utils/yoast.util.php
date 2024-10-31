<?php

function phantomwriter_client_verify_if_yoast_is_active()
{
	$plugins = apply_filters('active_plugins', get_option('active_plugins'));
	$result = false;

	if (in_array('wordpress-seo/wp-seo.php', $plugins) || in_array('wordpress-seo-premium/wp-seo-premium.php', $plugins)) {
		if (is_plugin_active('wordpress-seo/wp-seo.php') || is_plugin_active('wordpress-seo-premium/wp-seo-premium.php')) {
			$result = true;
		}
	}

	return apply_filters('phantomwriter_client_verify_if_yoast_is_active', $result);
}
