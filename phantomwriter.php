<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.widgilabs.com
 * @since             1.0.0
 * @package           Phantomwriter
 *
 * @wordpress-plugin
 * Plugin Name:       Phantom Writer
 * Plugin URI:        https://phantomwriter.ai
 * Description:       Increase by up to 95% your productivity by using AI within your WordPress website
 * Version:           2.2.0
 * Author:            Widgilabs
 * Author URI:        https://www.widgilabs.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       phantomwriter
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

// Global constants.
if (!defined('PHANTOMWRITER_CLIENT_PLUGIN_DIR')) {
	define('PHANTOMWRITER_CLIENT_PLUGIN_DIR', plugin_dir_path(__FILE__));
}

if (!defined('PHANTOMWRITER_CLIENT_PLUGIN_URL')) {
	define('PHANTOMWRITER_CLIENT_PLUGIN_URL', plugin_dir_url(__FILE__));
}

if (!defined('PHANTOMWRITER_CLIENT_PLUGIN_FILE')) {
	define('PHANTOMWRITER_CLIENT_PLUGIN_FILE', __FILE__);
}

if (!defined('PHANTOMWRITER_CLIENT_PLUGIN_BASENAME')) {
	define('PHANTOMWRITER_CLIENT_PLUGIN_BASENAME', plugin_basename(__FILE__));
}

if (!defined('PHANTOMWRITER_CLIENT_SETTINGS_URL')) {
	define('PHANTOMWRITER_CLIENT_SETTINGS_URL', admin_url('admin.php?page=phantomwriter'));
}

if (!defined('PHANTOMWRITER_CLIENT_API')) {
	define('PHANTOMWRITER_CLIENT_API', phantomwriter_client_get_api_url());
}

// Add composer autoloader.
$phantomwriter_client_vendor = PHANTOMWRITER_CLIENT_PLUGIN_DIR . 'vendor/autoload.php';

if (file_exists($phantomwriter_client_vendor)) {
	require_once $phantomwriter_client_vendor;
	add_action('after_setup_theme', function () {
		\Carbon_Fields\Carbon_Fields::boot();
	});
} else {
	wp_die('This plugin require composer. Please run `composer install` in the plugin directory or contact the Development Team.');
}

if (!function_exists('phantomwriter_client_fs')) {
	// Create a helper function for easy SDK access.
	function phantomwriter_client_fs()
	{
		global $phantomwriter_client_fs;

		if (!isset($phantomwriter_client_fs)) {
			// Include Freemius SDK.
			require_once PHANTOMWRITER_CLIENT_PLUGIN_DIR . '/freemius/start.php';

			$phantomwriter_client_fs = fs_dynamic_init(
				array(
					'id'                  => '13741',
					'slug'                => 'phantom-writer',
					'type'                => 'plugin',
					'public_key'          => 'pk_cda88ba9a9149b5cdc38f73d8cc8c',
					'is_premium'          => false,
					'has_addons'          => false,
					'has_paid_plans'      => false,
					'menu'                => array(
						'slug'           => 'phantomwriter',
						'first-path'     => 'admin.php?page=phantomwriter&welcome=true',
					),
					'enable_anonymous' 	=> false,
					'anonymous_mode' 	  => false,
				)
			);
		}

		return $phantomwriter_client_fs;
	}

	phantomwriter_client_fs();
	do_action('phantomwriter_client_fs_loaded');
}

if (!defined('PHANTOMWRITER_CLIENT_UPGRADE_LINK')) {
	define('PHANTOMWRITER_CLIENT_UPGRADE_LINK', phantomwriter_client_fs()->get_upgrade_url());
}


/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
if (!defined('PHANTOMWRITER_CLIENT_VERSION')) {
	define('PHANTOMWRITER_CLIENT_VERSION', '2.2.0');
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-phantomwriter-activator.php
 */
function phantomwriter_client_activate_plugin()
{
	require_once PHANTOMWRITER_CLIENT_PLUGIN_DIR . 'includes/class-phantomwriter-activator.php';
	Phantomwriter_Client_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-phantomwriter-deactivator.php
 */
function phantomwriter_client_deactivate_plugin()
{
	require_once PHANTOMWRITER_CLIENT_PLUGIN_DIR . 'includes/class-phantomwriter-deactivator.php';
	Phantomwriter_Client_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'phantomwriter_client_activate_plugin');
register_deactivation_hook(__FILE__, 'phantomwriter_client_deactivate_plugin');

function phantomwriter_client_get_api_url()
{
	if (defined('PHANTOMWRITER_CLIENT_API_URL')) return esc_url_raw(PHANTOMWRITER_CLIENT_API_URL);

	if (defined('WP_ENV')) {
		$env = strtolower(WP_ENV);
		$environments = [
			'local' => 'http://server.phantomwriter.widgilabs/',
			'dev'   => 'https://phantomwriter.dev.widgilabs-sites.com/',
			'prod'  => 'https://app.phantomwriter.ai/',
		];

		if (isset($environments[$env])) {
			return $environments[$env];
		}
	}

	return 'https://app.phantomwriter.ai/';
}

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
$phantomwriter_client_class = PHANTOMWRITER_CLIENT_PLUGIN_DIR . 'includes/class-phantomwriter.php';

if (file_exists($phantomwriter_client_class)) {
	require_once $phantomwriter_client_class;
}

$PHANTOMWRITER_CLIENT_API_class = PHANTOMWRITER_CLIENT_PLUGIN_DIR . 'api/class-phantomwriter-api.php';
if (file_exists($PHANTOMWRITER_CLIENT_API_class)) {
	require_once $PHANTOMWRITER_CLIENT_API_class;
}

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
if (!function_exists('phantomwriter_client_init')) {
	function phantomwriter_client_init()
	{
		$plugin = new Phantomwriter_Client();
		$plugin->phantomwriter_client_run();
	}
}
phantomwriter_client_init();

add_action('edit_form_top', 'wpdocs_display_hello');

function wpdocs_display_hello()
{
	echo __('hello world');
}
