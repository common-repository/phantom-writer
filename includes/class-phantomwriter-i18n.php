<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://www.widgilabs.com
 * @since      1.0.0
 *
 * @package    Phantomwriter
 * @subpackage Phantomwriter/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Phantomwriter
 * @subpackage Phantomwriter/includes
 * @author     Widgilabs <contact@widgilabs.com>
 */
class Phantomwriter_Client_i18n
{

	private $plugin_name;
	private $version;

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function __construct($plugin_name, $version)
	{
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function phantomwriter_client_load_plugin_textdomain()
	{
		load_plugin_textdomain(
			'phantomwriter',
			false,
			PHANTOMWRITER_CLIENT_PLUGIN_BASENAME . '/languages/'
		);
	}
}
