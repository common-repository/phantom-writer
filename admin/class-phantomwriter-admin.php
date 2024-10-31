<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.widgilabs.com
 * @since      1.0.0
 *
 * @package    Phantomwriter
 * @subpackage Phantomwriter/admin
 */

use Carbon_Fields\Container;
use Carbon_Fields\Field;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Phantomwriter
 * @subpackage Phantomwriter/admin
 * @author     Widgilabs <contact@widgilabs.com>
 */
class Phantomwriter_Client_Admin
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	private $api_url;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->api_url     = PHANTOMWRITER_CLIENT_API . 'wp-json/phantomwriter-server/v1/';
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function phantomwriter_client_enqueue_styles()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Phantomwriter_Client_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Phantomwriter_Client_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */


		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/phantomwriter-admin.css', array(), $this->version, 'all');

		wp_enqueue_style($this->plugin_name . '-tailwind', PHANTOMWRITER_CLIENT_PLUGIN_URL . 'assets/css/tailwind.css', array(), $this->version, 'all');

		$get_page = !empty($_GET['page']) ? sanitize_text_field($_GET['page']) : '';
		if ('phantomwriter-upgrades' === $get_page) {
			wp_enqueue_style('phantomwriter-upgrades', plugin_dir_url(__FILE__) . 'css/phantomwriter-admin-upgrades.css', array(), $this->version, 'all');
		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function phantomwriter_client_enqueue_scripts()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Phantomwriter_Client_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Phantomwriter_Client_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/phantomwriter-admin.js', array('jquery', 'wp-i18n'), $this->version, false);
		wp_localize_script(
			$this->plugin_name,
			'phantomwriter',
			array(
				'ajax_url'     => admin_url('admin-ajax.php'),
				'nonce'        => wp_create_nonce('phantomwriter-client-ajax-nonce'),
				'upgrade_link' => PHANTOMWRITER_CLIENT_UPGRADE_LINK
			)
		);
	}

	public function phantomwriter_client_add_action_links($links)
	{
		$settings = '<a href="' . esc_url(PHANTOMWRITER_CLIENT_SETTINGS_URL) . '">' . __('Settings', 'phantomwriter') . '</a>';
		$links = array_merge(array($settings), $links);
		return apply_filters('phantomwriter_client_action_links', $links);
	}

	public function phantomwriter_client_register_settings()
	{
		$settings = Container::make('theme_options', __('Phantom Writer', 'phantomwriter'));
		$settings->set_icon('dashicons-games');
		$settings->set_page_file($this->plugin_name);
		$settings->set_page_menu_title(
			__('Phantom Writer', 'phantomwriter')
		);

		$this->phantomwriter_client_add_tab_informative($settings);
		$this->phantomwriter_client_add_tab_settings($settings);
	}

	protected function phantomwriter_client_add_tab_informative(&$container)
	{
		$fields = array();

		$fields[] = Field::make('text', 'phantomwriter_total_words', __('Total Words', 'phantomwriter'))
			->set_attribute('readOnly', true)
			->set_default_value(0)
			->set_help_text(
				__('Total words that you have used', 'phantomwriter')
			);

		if (phantomwriter_client_fs()->is_free_plan()) {
			$fields[] = Field::make('text', 'phantomwriter_max_words_allowed', __('Words Limit', 'phantomwriter'))
				->set_attribute('readOnly', true)
				->set_default_value('2000')
				->set_help_text(
					__('The maximum number of words you can generate using the free plan', 'phantomwriter')
				);
		}

		$container->add_tab(
			__('Informative', 'phantomwriter'),
			$fields
		);
	}

	protected function phantomwriter_client_add_tab_settings(&$container)
	{
		$fields = array();

		$default   = phantomwriter_client_get_default_language();
		$languages = phantomwriter_client_get_languages();

		if (!empty($languages)) {
			$fields[] = Field::make('select', 'phantomwriter_client_language', __('Language', 'phantomwriter'))
				->add_options($languages)
				->set_help_text(
					__('Choose the language for the prompt to generate content', 'phantomwriter')
				)
				->set_default_value($default)
				->set_required(true);
		}

		$fields[] = Field::make( 'textarea', 'phantomwriter_client_profile', __('Prompt Brief', 'phantomwriter') )
			->set_help_text(
				__('Add a brief description of the content you want to generate', 'phantomwriter')
			)
			->set_attribute('placeholder', __('Add a brief description or command of the content you want to generate', 'phantomwriter'))
			->set_attribute('maxLength', 300)
			->set_rows( 5 )
			->set_required(false);

		$container->add_tab(
			__('Settings', 'phantomwriter'),
			$fields
		);
	}

	public function phantomwriter_client_should_save_field($save, $value, $field)
	{
		if ($save) {
			if ('_phantomwriter_client_language' === $field->get_name()) {
				if (empty($value) || false === $value) $value = 'en';

				$language_name = phantomwriter_client_get_language_by_code($value);
				if (!empty($language_name)) update_option('_phantomwriter_client_language_name', $language_name);
			}
		}
		return $save;
	}

	/**
	 * Show admin notices
	 *
	 * @since 2.2.0
	 */
	function phantomwriter_client_admin_notices()
	{
		$notice = Phantomwriter_Client_Notices::phantomwriter_client_get_instance();
		$notices = $notice::phantomwriter_client_get_notices();

		if (!empty($notices) && count($notices) > 0) {
			foreach ($notices as $warn) {
				$message = $warn['message'] ?? '';
				$type = $warn['type'] ?? 'error';

				if (!empty($message)) {
					$message = sprintf(
						'<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
						$type,
						$message
					);
					echo wp_kses_post($message);
				}
			}
		} else {
			return;
		}
	}
}
