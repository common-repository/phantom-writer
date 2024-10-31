<?php

/**
 * Fired during plugin activation
 *
 * @link       https://www.widgilabs.com
 * @since      1.0.0
 *
 * @package    Phantomwriter
 * @subpackage Phantomwriter/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Phantomwriter
 * @subpackage Phantomwriter/includes
 * @author     Widgilabs <contact@widgilabs.com>
 */
class Phantomwriter_Client_Activator
{

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate()
	{
		add_option('phantomwriter_client_do_activation_redirect', true);
	}
}
