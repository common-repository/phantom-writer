<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.widgilabs.com
 * @since      1.0.0
 *
 * @package    Phantomwriter
 * @subpackage Phantomwriter/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Phantomwriter
 * @subpackage Phantomwriter/includes
 * @author     Widgilabs <contact@widgilabs.com>
 */
class Phantomwriter_Client
{

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Phantomwriter_Client_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct()
	{
		if (defined('PHANTOMWRITER_CLIENT_VERSION')) {
			$this->version = PHANTOMWRITER_CLIENT_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'phantomwriter';

		$this->phantomwriter_client_load_dependencies();
		$this->phantomwriter_client_set_locale();
		$this->phantomwriter_client_define_utils_functions();
		$this->phantomwriter_client_define_admin_hooks();
		$this->phantomwriter_client_define_public_hooks();
		$this->phantomwriter_client_define_cpt_hooks();
		$this->phantomwriter_client_define_metabox_hooks();
		$this->phantomwriter_client_define_ajax_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Phantomwriter_Client_Loader. Orchestrates the hooks of the plugin.
	 * - Phantomwriter_Client_i18n. Defines internationalization functionality.
	 * - Phantomwriter_Client_Admin. Defines all hooks for the admin area.
	 * - Phantomwriter_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function phantomwriter_client_load_dependencies()
	{

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once PHANTOMWRITER_CLIENT_PLUGIN_DIR . 'includes/class-phantomwriter-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once PHANTOMWRITER_CLIENT_PLUGIN_DIR . 'includes/class-phantomwriter-i18n.php';

		/**
		 * The class responsible for defining notices functionality
		 * of the plugin.
		 */
		require_once PHANTOMWRITER_CLIENT_PLUGIN_DIR . 'includes/class-phantomwriter-notices.php';

		/**
		 * The class responsible for the utils functionality
		 * of the plugin.
		 */
		require_once PHANTOMWRITER_CLIENT_PLUGIN_DIR . 'includes/class-phantomwriter-utils.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once PHANTOMWRITER_CLIENT_PLUGIN_DIR . 'admin/class-phantomwriter-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once PHANTOMWRITER_CLIENT_PLUGIN_DIR . 'public/class-phantomwriter-public.php';

		/**
		 * The class responsible for defining all actions that occur in the custom post type
		 * side of the site.
		 */
		require_once PHANTOMWRITER_CLIENT_PLUGIN_DIR . 'includes/class-phantomwriter-cpt.php';

		/**
		 * The class responsible for defining all actions that occur in the ajax
		 * side of the site.
		 */
		require_once PHANTOMWRITER_CLIENT_PLUGIN_DIR . 'api/class-phantomwriter-ajax.php';

		/**
		 * The class responsible for defining all actions that occur in the metabox
		 * side of the site.
		 */
		require_once PHANTOMWRITER_CLIENT_PLUGIN_DIR . 'admin/class-phantomwriter-metabox.php';

		$this->loader = new Phantomwriter_Client_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Phantomwriter_Client_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function phantomwriter_client_set_locale()
	{

		$plugin_i18n = new Phantomwriter_Client_i18n($this->phantomwriter_client_get_plugin_name(), $this->phantomwriter_client_get_version());

		$this->loader->phantomwriter_client_add_action('plugins_loaded', $plugin_i18n, 'phantomwriter_client_load_plugin_textdomain');
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function phantomwriter_client_define_admin_hooks()
	{

		$plugin_admin = new Phantomwriter_Client_Admin($this->phantomwriter_client_get_plugin_name(), $this->phantomwriter_client_get_version());

		$this->loader->phantomwriter_client_add_action('admin_notices', $plugin_admin, 'phantomwriter_client_admin_notices');
		$this->loader->phantomwriter_client_add_action('admin_enqueue_scripts', $plugin_admin, 'phantomwriter_client_enqueue_styles');
		$this->loader->phantomwriter_client_add_action('admin_enqueue_scripts', $plugin_admin, 'phantomwriter_client_enqueue_scripts');
		$this->loader->phantomwriter_client_add_filter('plugin_action_links_' . PHANTOMWRITER_CLIENT_PLUGIN_BASENAME, $plugin_admin, 'phantomwriter_client_add_action_links');
		$this->loader->phantomwriter_client_add_action('carbon_fields_register_fields', $plugin_admin, 'phantomwriter_client_register_settings');
		$this->loader->phantomwriter_client_add_filter('carbon_fields_should_save_field_value', $plugin_admin, 'phantomwriter_client_should_save_field', 10, 3);
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function phantomwriter_client_define_public_hooks()
	{

		$plugin_public = new Phantomwriter_Public($this->phantomwriter_client_get_plugin_name(), $this->phantomwriter_client_get_version());

		$this->loader->phantomwriter_client_add_action('wp_enqueue_scripts', $plugin_public, 'phantomwriter_client_enqueue_styles');
		$this->loader->phantomwriter_client_add_action('wp_enqueue_scripts', $plugin_public, 'phantomwriter_client_enqueue_scripts');
		$this->loader->phantomwriter_client_add_action('admin_init', $plugin_public, 'redirect_on_activate');
	}

	/**
	 * Register all of the hooks related to the metabox functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */

	private function phantomwriter_client_define_metabox_hooks()
	{

		$plugin_metabox = new Phantomwriter_Client_Metabox($this->phantomwriter_client_get_plugin_name(), $this->phantomwriter_client_get_version());

		$this->loader->phantomwriter_client_add_action('add_meta_boxes', $plugin_metabox, 'phantomwriter_client_add_metabox_to_all_posts');
	}

	/**
	 * Register all of the hooks related to the custom post type functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function phantomwriter_client_define_cpt_hooks()
	{

		$plugin_cpt = new Phantomwriter_Client_Cpt($this->phantomwriter_client_get_plugin_name(), $this->phantomwriter_client_get_version());

		$this->loader->phantomwriter_client_add_action('init', $plugin_cpt, 'phantomwriter_client_cpt_results');
		$this->loader->phantomwriter_client_add_action('carbon_fields_register_fields', $plugin_cpt, 'phantomwriter_client_cpt_results_add_meta_box');
		$this->loader->phantomwriter_client_add_filter('manage_phantom_results_posts_columns', $plugin_cpt, 'phantomwriter_client_results_table_head', 11);
		$this->loader->phantomwriter_client_add_action('manage_phantom_results_posts_custom_column', $plugin_cpt, 'phantomwriter_client_results_table_content', 11, 2);
	}

	/**
	 * Register all of the hooks related to the ajax functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function phantomwriter_client_define_ajax_hooks()
	{

		$plugin_ajax = new Phantomwriter_Client_Ajax($this->phantomwriter_client_get_plugin_name(), $this->phantomwriter_client_get_version());

		$this->loader->phantomwriter_client_add_action('wp_ajax_phantomwriter_client_get_content', $plugin_ajax, 'phantomwriter_client_get_content');
		$this->loader->phantomwriter_client_add_action('wp_ajax_nopriv_phantomwriter_client_get_content', $plugin_ajax, 'phantomwriter_client_get_content');
		$this->loader->phantomwriter_client_add_action('wp_ajax_phantomwriter_client_result_modal_action', $plugin_ajax, 'phantomwriter_client_result_modal_action');
		$this->loader->phantomwriter_client_add_action('wp_ajax_nopriv_phantomwriter_client_result_modal_action', $plugin_ajax, 'phantomwriter_client_result_modal_action');
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function phantomwriter_client_run()
	{
		$this->loader->phantomwriter_client_run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function phantomwriter_client_get_plugin_name()
	{
		return $this->plugin_name;
	}

	/**
	 * The function that will load the utils functions
	 *
	 * @since     1.1.0
	 */
	private function phantomwriter_client_define_utils_functions()
	{
		$utils = new Phantomwriter_Client_Utils();
		$utils->phantomwriter_client_init();
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Phantomwriter_Client_Loader    Orchestrates the hooks of the plugin.
	 */
	public function phantomwriter_client_get_loader()
	{
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function phantomwriter_client_get_version()
	{
		return $this->version;
	}
}
