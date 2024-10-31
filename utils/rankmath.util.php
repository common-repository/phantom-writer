<?php

function phantomwriter_client_verify_if_rankmath_is_active()
{
	$plugins = apply_filters('active_plugins', get_option('active_plugins'));
	$result = false;

	if (in_array('seo-by-rank-math/rank-math.php', $plugins)) {
		if (is_plugin_active('seo-by-rank-math/rank-math.php')) {
			$result = true;
		}
	}

	return apply_filters('phantomwriter_client_verify_if_rankmath_is_active', $result);
}
