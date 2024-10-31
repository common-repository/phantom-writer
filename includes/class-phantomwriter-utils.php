<?php

/**
 * Fired during plugin activation
 *
 * @link       https://www.widgilabs.com
 * @since      1.1.0
 *
 * @package    Phantomwriter
 * @subpackage Phantomwriter/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all the utils functions.
 *
 * @since      1.1.0
 * @package    Phantomwriter
 * @subpackage Phantomwriter/includes
 * @author     Widgilabs <contact@widgilabs.com>
 */
class Phantomwriter_Client_Utils
{

	/**
	 * Load the util functions
	 *
	 * @since    1.0.0
	 */
	public static function phantomwriter_client_init()
	{
		$files = glob(PHANTOMWRITER_CLIENT_PLUGIN_DIR . 'utils/*.util.php');

		if (empty($files)) return;

		foreach ($files as $file) {
			if (file_exists($file)) require_once $file;
		}
	}
}
