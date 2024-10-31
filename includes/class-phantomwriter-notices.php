<?php

class Phantomwriter_Client_Notices
{
	private static $phantomwriter_client_notices_instance;

	private function __construct()
	{
	}

	private function __clone()
	{
	}

	public static function phantomwriter_client_get_instance()
	{
		if (self::$phantomwriter_client_notices_instance === null) {
			self::$phantomwriter_client_notices_instance = new self;
		}
		return self::$phantomwriter_client_notices_instance;
	}

	public static function phantomwriter_client_add_notice($message, $type = 'error')
	{
		$notices[] = array(
			'type'    => $type,
			'message' => $message,
		);
		set_transient('phantomwriter_client_shop_notices', $notices, 5);
	}

	public static function phantomwriter_client_get_notices()
	{
		$notices = get_transient('phantomwriter_client_shop_notices');
		if (!$notices) {
			$notices = array();
		}
		return $notices;
	}
}
