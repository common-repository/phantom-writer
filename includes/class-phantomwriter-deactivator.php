<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://www.widgilabs.com
 * @since      1.0.0
 *
 * @package    Phantomwriter
 * @subpackage Phantomwriter/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Phantomwriter
 * @subpackage Phantomwriter/includes
 * @author     Widgilabs <contact@widgilabs.com>
 */
class Phantomwriter_Client_Deactivator
{

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate()
	{
		delete_option('phantomwriter_client_do_activation_redirect');
	}
}
