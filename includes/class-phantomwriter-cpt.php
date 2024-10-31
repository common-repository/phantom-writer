<?php

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class Phantomwriter_Client_Cpt
{

	public $plugin_name;

	public $version;

	public function __construct($plugin_name, $version)
	{
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	function phantomwriter_client_cpt_results()
	{
		$labels = array(
			'name'               => _x('Phantom Results', 'post type general name', 'phantomwriter'),
			'singular_name'      => _x('Phantom Results', 'post type singular name', 'phantomwriter'),
			'add_new'            => _x('Add New', 'phantomwriter'),
			'add_new_item'       => __('Add New Phantom Results', 'phantomwriter'),
			'edit_item'          => __('Edit Phantom Results', 'phantomwriter'),
			'new_item'           => __('New Phantom Results', 'phantomwriter'),
			'all_items'          => __('All Phantom Results', 'phantomwriter'),
			'view_item'          => __('View Phantom Results', 'phantomwriter'),
			'search_items'       => __('Search Phantom Results', 'phantomwriter'),
			'not_found'          => __('No Phantom Results found', 'phantomwriter'),
			'not_found_in_trash' => __('No Phantom Results found in the Trash', 'phantomwriter'),
			'parent_item_colon'  => '',
			'menu_name'          => __('Phantom Results', 'phantomwriter'),
		);

		$args = array(
			'labels'             => $labels,
			'description'        => __('Holds our News and News specific data', 'phantomwriter'),
			'public'             => true,
			'menu_position'      => 5,
			'menu_icon'          => 'dashicons-archive',
			'publicly_queryable' => false,
			'query_var'          => false,
			'rewrite'            => array('slug' => 'phantom_results'),
			'supports'           => array(),
			'has_archive'        => false,
			'show_in_rest'       => false,
			'capability_type'    => 'post',
			'capabilities'       => array(
				'create_posts' => false,
			),
			'map_meta_cap' => true,
		);

		register_post_type('phantom_results', $args);
		remove_post_type_support('phantom_results', 'author');
		remove_post_type_support('phantom_results', 'comments');
		remove_post_type_support('phantom_results', 'excerpt');
		remove_post_type_support('phantom_results', 'editor');
		remove_post_type_support('phantom_results', 'thumbnail');
		remove_post_type_support('phantom_results', 'custom-fields');
		remove_post_type_support('phantom_results', 'page-attributes');
	}

	public function phantomwriter_client_results_table_head($defaults)
	{
		unset($defaults['date']);
		$defaults['words_count'] = __('Words Count', 'phantomwriter');
		return $defaults;
	}

	public function phantomwriter_client_results_table_content($column_name, $post_id)
	{
		if ('words_count' === $column_name) {
			$words_count = get_post_meta($post_id, '_words_count', true);
			$column_content = sprintf(
				'<span style="padding: 4px; background-color: #DDD; border-radius: 4px;">%s</span>',
				esc_html($words_count)
			);
			echo wp_kses_post($column_content);
		}
	}

	public function phantomwriter_client_cpt_results_add_meta_box()
	{
		$container = Container::make('post_meta', __('Results', 'phantomwriter'))
			->where('post_type', '=', 'phantom_results');

		$container->add_tab(
			__('Informative', 'phantomwriter'),
			array(
				Field::make('text', 'language', __('Language', 'phantomwriter'))
					->set_attribute('readOnly', true)
					->set_default_value('Unknown'),
				Field::make('textarea', 'prompt', __('Prompt', 'phantomwriter'))
					->set_attribute('readOnly', true),
				Field::make('textarea', 'profile', __('Profile', 'phantomwriter'))
					->set_attribute('readOnly', true),
				Field::make('text', 'words_count', __('Words Count', 'phantomwriter'))
					->set_attribute('readOnly', true),
			)
		);

		$container->add_tab(
			__('Post', 'phantomwriter'),
			array(
				Field::make('text', 'title', __('Title', 'phantomwriter'))
					->set_attribute('readOnly', true),
				Field::make('textarea', 'content', __('Content', 'phantomwriter'))
					->set_attribute('readOnly', true),
				Field::make('textarea', 'excerpt', __('Excerpt', 'phantomwriter'))
					->set_attribute('readOnly', true),
			)
		);

		$container->add_tab(
			__('Image Idea', 'phantomwriter'),
			array(
				Field::make('textarea', 'image_idea', __('Content', 'phantomwriter'))
					->set_attribute('readOnly', true),
			)
			);

		$this->phantomwriter_client_cpt_results_add_yoast_meta_box($container);
		$this->phantomwriter_client_cpt_results_add_rankmath_meta_box($container);
	}

	protected function phantomwriter_client_cpt_results_add_yoast_meta_box(&$container)
	{
		$post_id = !empty($_GET['post']) ? sanitize_text_field($_GET['post']) : null;

		if (empty($post_id)) return;

		$yoast_wpseo_title = get_post_meta($post_id, '_yoast_wpseo_title', true);
		$yoast_wpseo_desc  = get_post_meta($post_id, '_yoast_wpseo_metadesc', true);

		if (empty($yoast_wpseo_title) && empty($yoast_wpseo_desc)) return;

		$container->add_tab(
			__('SEO', 'phantomwriter'),
			array(
				Field::make('text', 'yoast_wpseo_title', __('Yoast Title', 'phantomwriter'))
					->set_attribute('readOnly', true),
				Field::make('textarea', 'yoast_wpseo_desc', __('Yoast Description', 'phantomwriter'))
					->set_attribute('readOnly', true),
				Field::make('text', 'yoast_wpseo_focuskw', __('Yoast Focus Keyword', 'phantomwriter'))
					->set_attribute('readOnly', true),
			)
		);
	}

	protected function phantomwriter_client_cpt_results_add_rankmath_meta_box(&$container)
	{
		$post_id = !empty($_GET['post']) ? sanitize_text_field($_GET['post']) : null;

		if (empty($post_id)) return;

		$yoast_wpseo_title = get_post_meta($post_id, '_rank_math_title', true);
		$yoast_wpseo_desc  = get_post_meta($post_id, '_rank_math_description', true);

		if (empty($yoast_wpseo_title) && empty($yoast_wpseo_desc)) return;

		$container->add_tab(
			__('SEO', 'phantomwriter'),
			array(
				Field::make('text', 'rank_math_title', __('Rank Math Title', 'phantomwriter'))
					->set_attribute('readOnly', true),
				Field::make('textarea', 'rank_math_description', __('Rank Math Description', 'phantomwriter'))
					->set_attribute('readOnly', true),
				Field::make('text', 'rank_math_focus_keyword', __('Rank Math Focus Keyword', 'phantomwriter'))
			)
		);
	}
}
