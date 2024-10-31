<?php

class Phantomwriter_Client_Ajax
{

	public $plugin_name;

	public $version;

	protected $api_url;

	public function __construct($plugin_name, $version)
	{
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->api_url     = PHANTOMWRITER_CLIENT_API . 'wp-json/phantomwriter-server/v1/';
	}

	protected function phantomwriter_client_remove_quotes($string, $quotes = '"')
	{
		if (empty($string)) {
			return false;
		}

		if ($quotes === substr($string, 0, 1)) {
			$string = substr($string, 1);
		}

		if ($quotes === substr($string, -1)) {
			$string = substr($string, 0, -1);
		}

		$string = trim($string);
		return sanitize_text_field($string);
	}

	protected function phantomwriter_client_should_generate_override($verify)
	{
		switch ($verify) {
			case 'add':
			case 'override':
			case 'generate':
				return true;
			default:
				return false;
		}
	}

	protected function phantomwriter_cliente_parse_seo($content)
	{
		if (empty($content)) return false;

		if (phantomwriter_client_verify_if_rankmath_is_active() && !phantomwriter_client_verify_if_yoast_is_active()) {
			$content = str_replace('%%sitename%%', '%sitename%', $content);
			$content = str_replace('%%sep%%', '%sep%', $content);
		}

		$content = $this->phantomwriter_client_remove_quotes($content);
		return sanitize_text_field($content);
	}

	protected function phantomwriter_client_verify()
	{
		$remote = wp_remote_post(
			$this->api_url . 'verify',
			array(
				'timeout' => 3600,
				'body' => json_encode(
					array(
						'url'            => get_site_url(),
						'site_id'        => get_current_blog_id(),
						'is_pro'         => phantomwriter_client_fs()->can_use_premium_code() ? true : false,
						'plugin_version' => PHANTOMWRITER_CLIENT_VERSION ?? $this->version,
					)
				),
				'headers' => array(
					'content-type' => 'application/json',
				),
			)
		);

		if (is_wp_error($remote)) {
			return $remote;
		}

		$response = json_decode(
			wp_remote_retrieve_body($remote)
		);

		if (!$response->success) {
			return new WP_Error(
				'phantomwriter_client_verify',
				$response->data
			);
		}

		$max_words_allowed = $response->data->max_words_allowed;
		if (!empty($max_words_allowed) && $max_words_allowed !== get_option('_phantomwriter_max_words_allowed')) {
			update_option('_phantomwriter_max_words_allowed', $max_words_allowed);
		}

		return true;
	}

	public function phantomwriter_client_get_content()
	{
		$nonce = sanitize_text_field($_POST['nonce']);

		if (!wp_verify_nonce($nonce, 'phantomwriter-client-ajax-nonce')) {
			return wp_send_json_error(
				__('Invalid nonce', 'phantomwriter')
			);
		}

		$prompt                = sanitize_text_field($_POST['prompt']);
		$length                = sanitize_text_field($_POST['length']) ?? 150;
		$lang_code             = sanitize_text_field($_POST['languageCode']) ?? get_option('_phantomwriter_client_language', 'en');
		$override_seo_title    = sanitize_text_field($_POST['overrideSeoTitle']) ?? false;
		$override_seo_desc     = sanitize_text_field($_POST['overrideSeoDesc']) ?? false;
		$override_seo_keywords = sanitize_text_field($_POST['overrideSeoKeywords']) ?? false;
		$override_title        = sanitize_text_field($_POST['overrideTitle']) ?? false;
		$override_content      = sanitize_text_field($_POST['overrideDescription']) ?? false;
		$override_excerpt      = sanitize_text_field($_POST['overrideExcerpt']) ?? false;
		$generate_image_idea   = sanitize_text_field($_POST['generateImageIdea']) ?? false;
		$lang_name             = phantomwriter_client_get_language_by_code($lang_code);
		$site_id               = get_current_blog_id();
		$is_free               = phantomwriter_client_fs()->is_free_plan() ?? true;
		$is_pro                = phantomwriter_client_fs()->can_use_premium_code() ?? false;
		$is_paying             = phantomwriter_client_fs()->is_paying() ?? false;
		$is_trial              = phantomwriter_client_fs()->is_trial() ?? false;
		$profile               = get_option('_phantomwriter_client_profile');
		$redirect 			       = false;

		if (empty($prompt)) return wp_send_json_error(__('No prompt found', 'phantomwriter'));
		if (empty($site_id)) return wp_send_json_error(__('No site id found', 'phantomwriter'));

		$verify_subscription = $this->phantomwriter_client_verify();

		if (is_wp_error($verify_subscription)) return wp_send_json_error($verify_subscription->get_error_message());

		if ($is_free) {
			$words_count   = get_option('_phantomwriter_total_words', 0);
			$words_allowed = get_option('_phantomwriter_max_words_allowed', 2000);
			$total = intval($words_count) + intval($length);
			if ($words_allowed < $total) {
				$limit = $words_allowed - intval($words_count);

				if ($limit < 0) return wp_send_json_error(__('You have reached your word limit for the free tier.', 'phantomwriter'));

				return wp_send_json_error(
					sprintf(
						__('You have reached your word limit for the free tier. you can only use a length less than or equal to %s.', 'phantomwriter'),
						$limit
					)
				);
			}
		}

		$generate_seo_title    = $this->phantomwriter_client_should_generate_override($override_seo_title);
		$generate_seo_desc     = $this->phantomwriter_client_should_generate_override($override_seo_desc);
		$generate_seo_keywords = $this->phantomwriter_client_should_generate_override($override_seo_keywords);
		$generate_title        = $this->phantomwriter_client_should_generate_override($override_title);
		$generate_content      = $this->phantomwriter_client_should_generate_override($override_content);
		$generate_excerpt      = $this->phantomwriter_client_should_generate_override($override_excerpt);
		$generate_image_idea   = $this->phantomwriter_client_should_generate_override($generate_image_idea);

		$remote = wp_remote_post(
			$this->api_url . 'generate',
			array(
				'timeout' => 3600,
				'body' => json_encode(
					array(
						'url'                   => get_site_url(),
						'site_id'               => $site_id,
						'is_free'               => $is_free,
						'is_pro'                => $is_pro,
						'is_paying'             => $is_paying,
						'is_trial'              => $is_trial,
						'prompt'                => $prompt,
						'length'                => $length,
						'language_code'         => $lang_code,
						'language_name'         => $lang_name,
						'generate_seo_title'    => $generate_seo_title,
						'generate_seo_desc'     => $generate_seo_desc,
						'generate_seo_keywords' => $generate_seo_keywords,
						'generate_title'        => $generate_title,
						'generate_content'      => $generate_content,
						'generate_excerpt'      => $generate_excerpt,
						'generate_image_idea'   => $generate_image_idea,
						'profile'               => $profile,
						'plugin_version'        => PHANTOMWRITER_CLIENT_VERSION ?? $this->version,
					)
				),
				'headers' => array(
					'content-type' => 'application/json',
				),
			)
		);

		if (is_wp_error($remote)) {
			return wp_send_json_error(
				$remote->get_error_message()
			);
		}

		$response = json_decode(
			wp_remote_retrieve_body($remote)
		);

		if (!$response->success) {
			return wp_send_json_error($response->data);
		}

		if (empty($response->data)) {
			return wp_send_json_error(
				__('No data found', 'phantomwriter')
			);
		}

		$title      = $this->phantomwriter_client_remove_quotes($response->data->title);
		$excerpt    = $this->phantomwriter_client_remove_quotes($response->data->excerpt);
		$content    = $this->phantomwriter_client_remove_quotes($response->data->content);
		$image_idea = $this->phantomwriter_client_remove_quotes($response->data->image_idea);

		if (empty($content)) {
			return wp_send_json_error(
				__('No content found', 'phantomwriter')
			);
		}

		$seo          = $response->data->seo ?? false;
		$seo_title    = !empty($seo) && !empty($seo->title) ? $this->phantomwriter_cliente_parse_seo($seo->title) : '';
		$seo_desc     = !empty($seo) && !empty($seo->description) ? $this->phantomwriter_cliente_parse_seo($seo->description) : '';
		$seo_keywords = !empty($seo) && !empty($seo->keywords) ? $this->phantomwriter_cliente_parse_seo($seo->keywords) : '';

		$words_count = intval($response->data->words_count);
		if (empty($words_count)) {
			return wp_send_json_error(
				__('No words count found', 'phantomwriter')
			);
		}

		$total_words = intval($response->data->total_words);
		if (empty($total_words)) {
			return wp_send_json_error(
				__('No total words found', 'phantomwriter')
			);
		}

		$max_words_allowed = intval($response->data->max_words_allowed);
		if (!empty($max_words_allowed) && $max_words_allowed !== get_option('_phantomwriter_max_words_allowed')) {
			update_option('_phantomwriter_max_words_allowed', $max_words_allowed);
		}

		$result_post_id = wp_insert_post(
			array(
				'post_title'     => $title,
				'post_content'   => $content,
				'post_status'    => 'publish',
				'post_type'      => 'phantom_results',
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
			)
		);

		if (is_wp_error($result_post_id)) {
			return wp_send_json_error(
				$result_post_id->get_error_message()
			);
		}

		if (!empty($result_post_id)) {
			update_post_meta($result_post_id, '_language', $lang_code);
			update_post_meta($result_post_id, '_prompt', $prompt);
			update_post_meta($result_post_id, '_title', $title);
			update_post_meta($result_post_id, '_content', $content);
			update_post_meta($result_post_id, '_excerpt', $excerpt);
			update_post_meta($result_post_id, '_words_count', $words_count);
			update_post_meta($result_post_id, '_profile', $profile);

			if (!empty($seo_title)) {
				if (phantomwriter_client_verify_if_rankmath_is_active() && !phantomwriter_client_verify_if_yoast_is_active()) {
					update_post_meta($result_post_id, '_rank_math_title', $seo_title);
				} elseif (phantomwriter_client_verify_if_yoast_is_active()) {
					update_post_meta($result_post_id, '_yoast_wpseo_title', $seo_title);
				}
			}

			if (!empty($seo_desc)) {
				if (phantomwriter_client_verify_if_rankmath_is_active() && !phantomwriter_client_verify_if_yoast_is_active()) {
					update_post_meta($result_post_id, '_rank_math_description', $seo_desc);
				} elseif (phantomwriter_client_verify_if_yoast_is_active()) {
					update_post_meta($result_post_id, '_yoast_wpseo_metadesc', $seo_desc);
				}
			}

			if (!empty($seo_keywords)) {
				if (phantomwriter_client_verify_if_rankmath_is_active() && !phantomwriter_client_verify_if_yoast_is_active()) {
					update_post_meta($result_post_id, '_rank_math_focus_keyword', $seo_keywords);
				} elseif (phantomwriter_client_verify_if_yoast_is_active()) {
					update_post_meta($result_post_id, '_yoast_wpseo_focuskw', $seo_keywords);
				}
			}

			if (!empty($image_idea)) {
				update_post_meta($result_post_id, '_image_idea', $image_idea);
			}
		}

		update_option('_phantomwriter_total_words', $total_words);

		$post_id = wp_insert_post(
			array(
				'post_title'     => $title,
				'post_content'   => $content,
				'post_excerpt'   => $excerpt,
				'post_status'    => 'publish',
				'post_type'      => 'post',
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
			)
		);

		if (is_wp_error($post_id)) {
			return wp_send_json_error(
				$post_id->get_error_message()
			);
		}

		if (!empty($post_id)) {
			if (!empty($seo_title)) {
				if (phantomwriter_client_verify_if_rankmath_is_active() && !phantomwriter_client_verify_if_yoast_is_active()) {
					update_post_meta($post_id, 'rank_math_title', $seo_title);
				} elseif (phantomwriter_client_verify_if_yoast_is_active()) {
					update_post_meta($post_id, '_yoast_wpseo_title', $seo_title);
				}
			}

			if (!empty($seo_desc)) {
				if (phantomwriter_client_verify_if_rankmath_is_active() && !phantomwriter_client_verify_if_yoast_is_active()) {
					update_post_meta($post_id, 'rank_math_description', $seo_desc);
				} elseif (phantomwriter_client_verify_if_yoast_is_active()) {
					update_post_meta($post_id, '_yoast_wpseo_metadesc', $seo_desc);
				}
			}

			if (!empty($seo_keywords)) {
				if (phantomwriter_client_verify_if_rankmath_is_active() && !phantomwriter_client_verify_if_yoast_is_active()) {
					update_post_meta($post_id, 'rank_math_focus_keyword', $seo_keywords);
				} elseif (phantomwriter_client_verify_if_yoast_is_active()) {
					update_post_meta($post_id, '_yoast_wpseo_focuskw', $seo_keywords);
				}
			}

			if (!empty($image_idea)) {
				update_post_meta($post_id, '_image_idea', $image_idea);
			}
			$redirect = get_edit_post_link($post_id, '&');
		}

		return wp_send_json_success(
			array(
				'result_post_id'  => $result_post_id,
				'language'        => $lang_name,
				'title'           => $title,
				'content'         => $content,
				'excerpt'         => $excerpt,
				'count'           => $total_words,
				'seo'             => $seo,
				'post_words'      => $words_count,
				'remaining_words' => $max_words_allowed - $total_words ?? 0,
				'redirect'        => $redirect ?? false,
			)
		);
	}

	public function phantomwriter_client_result_modal_action()
	{
		$nonce = sanitize_text_field($_POST['nonce']);

		if (!wp_verify_nonce($nonce, 'phantomwriter-client-ajax-nonce')) {
			return wp_send_json_error(
				__('Invalid nonce', 'phantomwriter')
			);
		}

		$post_id = sanitize_text_field($_POST['postID']);
		$type    = sanitize_text_field($_POST['type']);

		if (empty($post_id)) return wp_send_json_error(__('No post id found', 'phantomwriter'));
		if (empty($type)) return wp_send_json_error(__('No type found', 'phantomwriter'));

		$type = strtolower($type);
		switch ($type) {
			case 'prompt':
				$meta_key = '_prompt';
				break;
			case 'title':
				$meta_key = '_title';
				break;
			case 'content':
			case 'description':
				$meta_key = '_content';
				break;
			default:
				return wp_send_json_error(__('Invalid type', 'phantomwriter'));
		}

		$meta_value = get_post_meta($post_id, $meta_key, true);

		if (empty($meta_value)) return wp_send_json_error(__('No meta value found', 'phantomwriter'));

		return wp_send_json_success(
			array(
				'content' => $meta_value,
			)
		);
	}
}
