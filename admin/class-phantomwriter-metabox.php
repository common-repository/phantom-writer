<?php

class Phantomwriter_Client_Metabox
{

	public $plugin_name;

	public $version;

	public function __construct($plugin_name, $version)
	{
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	public function phantomwriter_client_add_metabox_to_all_posts()
	{
		add_meta_box(
			'phantomwriter_client_metabox',
			__('Phantom Writer', 'phantomwriter'),
			array($this, 'phantomwriter_client_render_metabox'),
			$this->get_metabox_screen(),
			'advanced',
			'high'
		);
	}

	public function get_metabox_screen()
	{
		$post_types = get_post_types(array('public' => true));
		$post_types = array_diff($post_types, array('attachment', 'phantom_results'));
		return apply_filters('phantomwriter_client_metabox_screen', $post_types);
	}

	protected function phantomwriter_client_truncate($string, $max_words = 50, $append = '...')
	{
		if ($max_words <= 0) return;

		$words = preg_split('/\s+/', $string);

		if (count($words) < $max_words) return $string;

		$words = array_slice($words, 0, $max_words);

		$lastWord = end($words);
		$lastChar = mb_substr($lastWord, -1);

		if (preg_match('/[.,!?;:]/u', $lastChar)) {
			$lastWord = preg_replace('/[^A-Za-z]/u', '', $lastWord);
			$words[count($words) - 1] = $lastWord;
		}

		$truncatedText = implode(' ', $words) . $append;


		return $truncatedText;
	}

	public function phantomwriter_client_render_metabox()
	{
		$post_id                      = get_the_ID() ?? sanitize_text_field($_GET['post']) ?? false;
		$verify_if_yoast_is_active    = phantomwriter_client_verify_if_yoast_is_active();
		$verify_if_rankmath_is_active = phantomwriter_client_verify_if_rankmath_is_active() && !phantomwriter_client_verify_if_yoast_is_active();
		$words_count                  = get_option('_phantomwriter_total_words', 0);
		$max_words_allowed            = get_option('_phantomwriter_max_words_allowed', 2000);
		$remaining_words              = $max_words_allowed - $words_count;
		$show_metabox                 = (phantomwriter_client_fs()->is_free_plan() && 0 > $remaining_words) ? false : true;
		$language_code						    = get_option('_phantomwriter_client_language', 'en');
		$language_name 						    = get_option('_phantomwriter_client_language_name', 'English');
		$languages                    = phantomwriter_client_get_languages();
		$tab_prompt 							    = 'phantomwriter-tab-prompt';
		$tab_results							    = 'phantomwriter-tab-results';
		$tab_image_idea						    = 'phantomwriter-tab-image-idea';
		$post_image_idea					    = get_post_meta($post_id, '_image_idea', true);

		$result_posts = get_posts(
			array(
				'post_type'      => 'phantom_results',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		$override_title_options   = array(
			'override' => __('Generate & Override', 'phantomwriter'),
			'add'      => __('Generate but don\'t override', 'phantomwriter'),
			'none'     => __('Don\'t generate', 'phantomwriter'),
		);
		$override_content_options	= array(
			'override' => __('Generate & Override', 'phantomwriter'),
			'add'      => __('Generate & Add', 'phantomwriter'),
		);
		$override_excerpt_options = array(
			'override' => __('Generate & Override', 'phantomwriter'),
			'add'      => __('Generate but don\'t override', 'phantomwriter'),
			'none'     => __('Don\'t generate', 'phantomwriter'),
		);

		$generate_image_idea_options = array(
			'none'     => __('Don\'t generate', 'phantomwriter'),
			'generate' => __('Generate & Override', 'phantomwriter'),
		);

		$generate_seo = array(
			'override' => __('Generate & Override', 'phantomwriter'),
			'none'     => __('Don\'t generate', 'phantomwriter'),
		);
?>
		<?php if ($show_metabox) : ?>
			<div id="phantomwriter-tabs-container">
				<div id="phantomwriter-tabs-notice" class="phantomwriter-pr-8 phantomwriter-pb-4 phantomwriter-hidden">
					<div id="phantomwriter-notice" class="phantomwriter-alert phantomwriter-mx-auto">
						<svg xmlns="http://www.w3.org/2000/svg" class="phantomwriter-stroke-current phantomwriter-shrink-0 phantomwriter-h-6 phantomwriter-w-6" fill="none" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
						</svg>
						<span>
							<?php _e('Something went wrong.', 'phantomwriter'); ?>
						</span>
					</div>
				</div>
				<div id="phantomwriter-tabs-btn" role="tablist" class="phantomwriter-rounded-t-md phantomwriter-rounded-none phantomwriter-bg-base-300 phantomwriter-max-w-sm phantomwriter-tabs-boxed phantomwriter-tabs phantomwriter-border-none">
					<a id="<?php esc_attr_e($tab_prompt) ?>-btn" role="tab" data-tab="<?php esc_attr_e($tab_prompt) ?>" class="phantomwriter-tab phantomwriter-tab-lg phantomwriter-tab-active">
						<?php _e('Prompt', 'phantomwriter'); ?>
					</a>
					<a id="<?php esc_attr_e($tab_results) ?>-btn" role="tab" data-tab="<?php esc_attr_e($tab_results) ?>" class="phantomwriter-tab phantomwriter-tab-lg">
						<?php _e('Results', 'phantomwriter'); ?>
					</a>
					<?php if ($post_image_idea) : ?>
						<a id="<?php esc_attr_e($tab_image_idea) ?>-btn" role="tab" data-tab="<?php esc_attr_e($tab_image_idea) ?>" class="phantomwriter-tab phantomwriter-tab-lg">
							<?php _e('Image Idea', 'phantomwriter'); ?>
						</a>
					<?php endif; ?>
				</div>
				<div id="phantomwriter-tabs" class="phantomwriter-pl-4 phantomwriter-py-4 phantomwriter-rounded-b-md phantomwriter-rounded-tr-md phantomwriter-w-full phantomwriter-bg-base-300">

					<?php if (phantomwriter_client_fs()->is_free_plan()) : ?>
						<div class="phantomwriter-w-full md:phantomwriter-max-w-md phantomwriter-p-2">
							<p>
								<?php
								printf(
									esc_html__('You have %s words left for the free trial verion.', 'phantomwriter'),
									'<span id="phantomwriter-words-count" class="phantomwriter-text-primary">' . esc_html($max_words_allowed - $words_count) . '</span>'
								);
								?>
							</p>
						</div>
					<?php endif; ?>

					<div id="<?php esc_attr_e($tab_prompt) ?>" class="phantomwriter-js-tab">
						<div class="phantomwriter-overflow-x-auto phantomwriter-max-h-max">

							<div class="phantomwriter-form-control phantomwriter-py-4">
								<label class="phantomwriter-label">
									<span class="phantomwriter-label-text">
										<?php _e('1. Enter your prompt here', 'phantomwriter'); ?>
									</span>
								</label>
								<textarea id="phantomwriter-prompt" class="phantomwriter-textarea phantomwriter-textarea-bordered phantomwriter-textarea-lg phantomwriter-w-full md:phantomwriter-max-w-md" placeholder="<?php _e('Enter your content here.', 'phantomwriter'); ?>" rows="5" required></textarea>
								<label class="phantomwriter-label">
									<span class="phantomwriter-label-text">
										<?php
										_e(
											'This will be the base for the generated content and title.',
											'phantomwriter'
										);
										?>
									</span>
								</label>
							</div>

							<div class="phantomwriter-form-control phantomwriter-py-4">
								<label class="phantomwriter-label">
									<span class="phantomwriter-label-text">
										<?php _e('2. Max response length', 'phantomwriter'); ?>
									</span>
								</label>
								<input id="phantomwriter-length" type="number" placeholder="300" class="phantomwriter-input phantomwriter-input-bordered phantomwriter-w-full md:phantomwriter-max-w-md" value="300" min="150" max="2000" />
								<label class="phantomwriter-label">
									<span class="phantomwriter-label-text">
										<?php _e('This will limit the number of words in the response.', 'phantomwriter'); ?>
									</span>
								</label>
							</div>

							<div class="phantomwriter-form-control phantomwriter-py-4">
								<label class="phantomwriter-label">
									<span class="phantomwriter-label-text">
										<?php _e('3. Select Language', 'phantomwriter'); ?>
									</span>
								</label>
								<select id="phantomwriter-language" class="phantomwriter-select phantomwriter-select-bordered phantomwriter-w-full md:phantomwriter-max-w-md">
									<?php
									if (!empty($languages)) :
										foreach ($languages as $key => $value) :
									?>
											<option value="<?php esc_attr_e($key); ?>" <?php selected($language_code, $key, true) ?>>
												<?php esc_html_e($value); ?>
											</option>
									<?php
										endforeach;
									endif;
									?>
								</select>
								<label class="phantomwriter-label">
									<span class="phantomwriter-label-text-alt">
										<?php printf(esc_html__('This will replace your current language (%s) for this prompt.', 'phantomwriter'), $language_name); ?>
									</span>
								</label>
							</div>

							<details class="phantomwriter-collapse phantomwriter-collapse-plus phantomwriter-w-full md:phantomwriter-max-w-md phantomwriter-py-4">
								<summary class="phantomwriter-visible phantomwriter-collapse-title phantomwriter-text-lg phantomwriter-font-medium phantomwriter-px-2 phantomwriter-my-0 phantomwriter-py-0">
									<?php _e('Advanced Options', 'phantomwriter'); ?>
								</summary>
								<div class="phantomwriter-collapse-content phantomwrit phantomwriter-px-2 phantomwriter-my-0 phantomwriter-py-0">

									<div class="phantomwriter-form-control phantomwriter-w-full phantomwriter-max-w-md phantomwriter-py-2">
										<label class="phantomwriter-label">
											<span class="phantomwriter-label-text">
												<?php _e('4. Override Title', 'phantomwriter'); ?>
											</span>
										</label>
										<select id="phantomwriter-override-title" class="phantomwriter-select phantomwriter-select-bordered">
											<?php
											if (!empty($override_title_options)) :
												foreach ($override_title_options as $key => $value) :
											?>
													<option value="<?php esc_attr_e($key); ?>" <?php selected('add', $key, true) ?>>
														<?php esc_html_e($value); ?>
													</option>
											<?php
												endforeach;
											endif;
											?>
										</select>
										<label class="phantomwriter-label">
											<span class="phantomwriter-label-text-alt">
												<?php _e('This option allows you to control if the generated title override or not the existing Post Title', 'phantomwriter'); ?>
											</span>
										</label>
									</div>

									<div class="phantomwriter-form-control phantomwriter-w-full phantomwriter-max-w-md phantomwriter-py-2">
										<label class="phantomwriter-label">
											<span class="phantomwriter-label-text">
												<?php _e('5. Override Content', 'phantomwriter'); ?>
											</span>
										</label>
										<select id="phantomwriter-override-content" class="phantomwriter-select phantomwriter-select-bordered">
											<?php
											if (!empty($override_content_options)) :
												foreach ($override_content_options as $key => $value) :
											?>
													<option value="<?php esc_attr_e($key); ?>" <?php selected('add', $key, true) ?>>
														<?php esc_html_e($value); ?>
													</option>
											<?php
												endforeach;
											endif;
											?>
										</select>
										<label class="phantomwriter-label">
											<span class="phantomwriter-label-text-alt">
												<?php _e('This option allows you to control if the generated content override or not the existing Post Content', 'phantomwriter'); ?>
											</span>
										</label>
									</div>

									<div class="phantomwriter-form-control phantomwriter-w-full phantomwriter-max-w-md phantomwriter-py-2">
										<label class="phantomwriter-label">
											<span class="phantomwriter-label-text">
												<?php _e('6. Override Excerpt', 'phantomwriter'); ?>
											</span>
										</label>
										<select id="phantomwriter-override-excerpt" class="phantomwriter-select phantomwriter-select-bordered">
											<?php
											if (!empty($override_excerpt_options)) :
												foreach ($override_excerpt_options as $key => $value) :
											?>
													<option value="<?php esc_attr_e($key); ?>" <?php selected('add', $key, true) ?>>
														<?php esc_html_e($value); ?>
													</option>
											<?php
												endforeach;
											endif;
											?>
										</select>
										<label class="phantomwriter-label">
											<span class="phantomwriter-label-text-alt">
												<?php _e('This option allows you to control if the generated content override or not the existing Post Excerpt', 'phantomwriter'); ?>
											</span>
										</label>
									</div>

									<div class="phantomwriter-form-control phantomwriter-w-full phantomwriter-max-w-md phantomwriter-py-2">
										<label class="phantomwriter-label">
											<span class="phantomwriter-label-text">
												<?php _e('7. Generate Image Idea', 'phantomwriter'); ?>
											</span>
										</label>
										<select id="phantomwriter-generate-image-idea" class="phantomwriter-select phantomwriter-select-bordered">
											<?php
											if (!empty($generate_image_idea_options)) :
												foreach ($generate_image_idea_options as $key => $value) :
											?>
													<option value="<?php esc_attr_e($key); ?>" <?php selected('add', $key, true) ?>>
														<?php esc_html_e($value); ?>
													</option>
											<?php
												endforeach;
											endif;
											?>
										</select>
										<label class="phantomwriter-label">
											<span class="phantomwriter-label-text-alt">
												<?php _e('This option allows you to control if we sould generate an Image Idea', 'phantomwriter'); ?>
											</span>
										</label>
									</div>

								</div>
							</details>

							<?php
							if (phantomwriter_client_fs()->can_use_premium_code()) :
								if ($verify_if_yoast_is_active || $verify_if_rankmath_is_active) :
									$text_collapse_title  = __('Yoast SEO Options', 'phantomwriter');
									$text_seo_title_label = __('This option allows you to control if the generated title override or not the existing Yoast Seo Title', 'phantomwriter');
									$text_seo_desc_label  = __('This option allows you to control if we sould generate a Yoast SEO Meta Description', 'phantomwriter');
									if ($verify_if_rankmath_is_active && !$verify_if_yoast_is_active) {
										$text_collapse_title  = __('Rank Math Options', 'phantomwriter');
										$text_seo_title_label = __('This option allows you to control if the generated title override or not the existing Rank Math Title', 'phantomwriter');
										$text_seo_desc_label  = __('This option allows you to control if we sould generate a Rank Math Meta Description', 'phantomwriter');
									}
							?>
									<details class="phantomwriter-collapse phantomwriter-collapse-plus phantomwriter-w-full md:phantomwriter-max-w-md phantomwriter-py-4">
										<summary class="phantomwriter-visible phantomwriter-collapse-title phantomwriter-text-lg phantomwriter-font-medium phantomwriter-px-2 phantomwriter-my-0 phantomwriter-py-0">
											<?php esc_html_e($text_collapse_title); ?>
										</summary>
										<div class="phantomwriter-collapse-content phantomwrit phantomwriter-px-2 phantomwriter-my-0 phantomwriter-py-0">

											<div class="phantomwriter-form-control phantomwriter-w-full phantomwriter-max-w-md phantomwriter-py-2">
												<label class="phantomwriter-label">
													<span class="phantomwriter-label-text">
														<?php _e('Generate SEO Title', 'phantomwriter'); ?>
													</span>
												</label>
												<select id="phantomwriter-override-seo-title" class="phantomwriter-select phantomwriter-select-bordered">
													<?php
													if (!empty($generate_seo)) :
														foreach ($generate_seo as $key => $value) :
													?>
															<option value="<?php esc_attr_e($key); ?>" <?php selected('none', $key, true) ?>>
																<?php esc_html_e($value); ?>
															</option>
													<?php
														endforeach;
													endif;
													?>
												</select>
												<label class="phantomwriter-label">
													<span class="phantomwriter-label-text-alt">
														<?php esc_html_e($text_seo_title_label); ?>
													</span>
												</label>
											</div>

											<div class="phantomwriter-form-control phantomwriter-w-full phantomwriter-max-w-md phantomwriter-py-2">
												<label class="phantomwriter-label">
													<span class="phantomwriter-label-text">
														<?php _e('Generate SEO Meta Description', 'phantomwriter'); ?>
													</span>
												</label>
												<select id="phantomwriter-override-seo-description" class="phantomwriter-select phantomwriter-select-bordered">
													<?php
													if (!empty($generate_seo)) :
														foreach ($generate_seo as $key => $value) :
													?>
															<option value="<?php esc_attr_e($key); ?>" <?php selected('none', $key, true) ?>>
																<?php esc_html_e($value); ?>
															</option>
													<?php
														endforeach;
													endif;
													?>
												</select>
												<label class="phantomwriter-label">
													<span class="phantomwriter-label-text-alt">
														<?php esc_html_e($text_seo_desc_label); ?>
													</span>
												</label>
											</div>

											<div class="phantomwriter-form-control phantomwriter-w-full phantomwriter-max-w-md phantomwriter-py-2">
												<label class="phantomwriter-label">
													<span class="phantomwriter-label-text">
														<?php _e('Generate SEO Meta Keywords', 'phantomwriter'); ?>
													</span>
												</label>
												<select id="phantomwriter-override-seo-keywords" class="phantomwriter-select phantomwriter-select-bordered">
													<?php
													if (!empty($generate_seo)) :
														foreach ($generate_seo as $key => $value) :
													?>
															<option value="<?php esc_attr_e($key); ?>" <?php selected('none', $key, true) ?>>
																<?php esc_html_e($value); ?>
															</option>
													<?php
														endforeach;
													endif;
													?>
												</select>
												<label class="phantomwriter-label">
													<span class="phantomwriter-label-text-alt">
														<?php esc_html_e($text_seo_desc_label); ?>
													</span>
												</label>
											</div>

										</div>
									</details>
							<?php
								endif;
							endif;
							?>

							<button id="phantomwriter-button" class="phantomwriter-btn phantomwriter-btn-secondary phantomwriter-text-secondary-content">
								<?php _e('Generate Post', 'phantomwriter'); ?>
							</button>

							<?php if (phantomwriter_client_fs()->is_free_plan() || phantomwriter_client_fs()->is_trial()) : ?>
								<a class="phantomwriter-btn phantomwriter-btn-accent phantomwriter-text-accent-content" href="<?php echo esc_url(PHANTOMWRITER_CLIENT_UPGRADE_LINK); ?>" target="_blank">
									<?php _e('Upgrade to Pro', 'phantomwriter'); ?>
								</a>
							<?php endif; ?>

						</div>
					</div>

					<div id="<?php esc_attr_e($tab_results) ?>" class="phantomwriter-js-tab phantomwriter-hidden">
						<div class="phantomwriter-overflow-x-auto phantomwriter-max-h-96">
							<table id="phantomwriter-table" class="phantomwriter-table phantomwriter-table-xs phantomwriter-table-pin-rows phantomwriter-table-pin-cols">
								<thead>
									<tr>
										<th></th>
										<td><?php _e('Post ID', 'phantomwriter'); ?></td>
										<td><?php _e('Language', 'phantomwriter'); ?></td>
										<td><?php _e('Prompt', 'phantomwriter'); ?></td>
										<td><?php _e('Title', 'phantomwriter'); ?></td>
										<td><?php _e('Content', 'phantomwriter'); ?></td>
										<td><?php _e('Words Count', 'phantomwriter'); ?></td>
										<th><?php _e('Actions (BETA)', 'phantomwriter'); ?></th>
									</tr>
								</thead>
								<tbody>
									<?php
									if (!empty($result_posts)) :
										foreach ($result_posts as $key => $result_post) :
											$post_id       = $result_post->ID;
											$prompt        = get_post_meta($post_id, '_prompt', true);
											$prompt        = $this->phantomwriter_client_truncate($prompt, 30);
											$prompt_lang   = get_post_meta($post_id, '_language', true);
											$prompt_lang   = empty($prompt_lang)
												? 'Unknown'
												: phantomwriter_client_get_language_by_code($prompt_lang);
											$prompt_title  = get_post_meta($post_id, '_title', true);
											$disable_title = empty($prompt_title) ? true : false;
											$prompt_title  = !empty($prompt_title) ? $prompt_title : __('(no title)', 'phantomwriter');
											$prompt_title  = $this->phantomwriter_client_truncate($prompt_title, 10);
											$prompt_cont   = get_post_meta($post_id, '_content', true);
											$prompt_cont   = $this->phantomwriter_client_truncate($prompt_cont, 50);
											$words_count   = get_post_meta($post_id, '_words_count', true);
									?>
											<tr>
												<th><?php esc_html_e($key + 1); ?></th>
												<td class="phantomwriter-mx-auto phantomwriter-text-center"><?php esc_html_e($post_id); ?></td>
												<td><?php esc_html_e($prompt_lang); ?></td>
												<td><?php esc_html_e($prompt); ?></td>
												<td><?php esc_html_e($prompt_title); ?></td>
												<td><?php esc_html_e($prompt_cont); ?></td>
												<td class="phantomwriter-mx-auto phantomwriter-text-center"><?php esc_html_e($words_count); ?></td>
												<th>
													<div class="phantomwriter-join phantomwriter-join-vertical phantomwriter-rounded-none phantomwriter-gap-2 phantomwriter-w-full">
														<button data-post-id="<?php esc_attr_e($post_id); ?>" class="phantomwriter-js-results-add-prompt phantomwriter-btn phantomwriter-btn-primary phantomwriter-btn-sm phantomwriter-rounded-md phantomwriter-join-item">
															<?php _e('Add Prompt', 'phantomwriter'); ?>
														</button>
														<?php if (!$disable_title) : ?>
															<button data-post-id="<?php esc_attr_e($post_id); ?>" class="phantomwriter-js-results-add-title phantomwriter-btn phantomwriter-btn-primary phantomwriter-btn-sm phantomwriter-rounded-md phantomwriter-join-item">
																<?php _e('Add Title', 'phantomwriter'); ?>
															</button>
														<?php endif; ?>
														<button data-post-id="<?php esc_attr_e($post_id); ?>" class="phantomwriter-js-results-add-content phantomwriter-btn phantomwriter-btn-primary phantomwriter-btn-sm phantomwriter-rounded-md phantomwriter-join-item">
															<?php _e('Add Content', 'phantomwriter'); ?>
														</button>
													</div>
												</th>
											</tr>
									<?php
										endforeach;
									endif;
									?>
								</tbody>
								<tfoot>
									<tr>
										<th></th>
										<td><?php _e('Post ID', 'phantomwriter'); ?></td>
										<td><?php _e('Language', 'phantomwriter'); ?></td>
										<td><?php _e('Prompt', 'phantomwriter'); ?></td>
										<td><?php _e('Title', 'phantomwriter'); ?></td>
										<td><?php _e('Content', 'phantomwriter'); ?></td>
										<td><?php _e('Words Count', 'phantomwriter'); ?></td>
										<th><?php _e('Actions (BETA)', 'phantomwriter'); ?></th>
									</tr>
								</tfoot>
							</table>
						</div>
					</div>

					<?php if ($post_image_idea) : ?>
					<div id="<?php esc_attr_e($tab_image_idea) ?>" class="phantomwriter-js-tab phantomwriter-hidden">
						<div class="phantomwriter-form-control phantomwriter-py-4 phantomwriter-flex phantomwriter-flex-col md:phantomwriter-flex-row md:phantomwriter-justify-between md:phantomwriter-items-center">
							<div class="phantomwriter-w-full md:phantomwriter-w-auto">
								<label class="phantomwriter-label">
									<span class="phantomwriter-label-text">
										<?php _e('Image Idea', 'phantomwriter'); ?>
									</span>
								</label>
								<textarea id="phantomwriter-image-idea" class="phantomwriter-textarea phantomwriter-textarea-bordered phantomwriter-textarea-lg phantomwriter-w-full md:phantomwriter-max-w-md" placeholder="<?php _e('Image idea here...', 'phantomwriter'); ?>" readonly><?php echo esc_html($post_image_idea); ?></textarea>
							</div>
							<div class="phantomwriter-mt-4 md:phantomwriter-mt-0">
								<button id="phantomwriter-copy-image-idea" class="phantomwriter-btn phantomwriter-btn-secondary">
									<?php _e('Copy', 'phantomwriter'); ?>
								</button>
							</div>
						</div>
					</div>
					<?php endif; ?>

				</div>
				<div id="phantomwriter-tabs-loading" class="phantomwriter-hidden phantomwriter-flex-col phantomwriter-items-center phantomwriter-justify-center phantomwriter-h-96">
					<span class="phantomwriter-loading phantomwriter-loading-bars phantomwriter-loading-lg phantomwriter-text-primary"></span>
					<div class="phantomwriter-flex phantomwriter-animate-pulse  phantomwriter-flex-row phantomwriter-flex-nowrap phantomwriter-gap-1.5">
						<p class="phantomwriter-text-lg phantomwriter-font-bold">
							<?php _e('Generating content', 'phantomwriter'); ?>
						</p>
						<span class="phantomwriter-loading phantomwriter-loading-dots phantomwriter-loading-xs phantomwriter-mt-2"></span>
					</div>
				</div>
				<dialog id="phantomwriter-loading-modal" class="phantomwriter-modal phantomwriter-modal-bottom sm:phantomwriter-modal-middle">
					<div class="phantomwriter-modal-box">
						<div class="phantomwriter-flex phantomwriter-flex-col phantomwriter-items-center phantomwriter-justify-center phantomwriter-h-96">
							<span class="phantomwriter-loading phantomwriter-loading-bars phantomwriter-loading-lg phantomwriter-text-primary"></span>
							<div class="phantomwriter-flex phantomwriter-animate-pulse  phantomwriter-flex-row phantomwriter-flex-nowrap phantomwriter-gap-1.5">
								<p class="phantomwriter-text-lg phantomwriter-font-bold">
									<?php _e('Generating content', 'phantomwriter'); ?>
								</p>
								<span class="phantomwriter-loading phantomwriter-loading-dots phantomwriter-loading-xs phantomwriter-mt-2"></span>
							</div>
							<span class="phantomwriter-text-red-500">
								<?php _e('Do not close/reload this window.', 'phantomwriter'); ?>
							</span>
						</div>
					</div>
				</dialog>
			</div>
		<?php else : ?>
			<div class="phantomwriter-card phantomwriter-w-full phantomwriter-mx-auto phantomwriter-bg-base-100 phantomwriter-text-primary">
				<div class="phantomwriter-card-body phantomwriter-items-center phantomwriter-text-center">
					<p class="phantomwriter-card-title phantomwriter-text-xl">
						<?php _e('You have reached the limit of words for the free version.', 'phantomwriter'); ?>
					</p>
					<p class="phantomwriter-text-lg">
						<?php _e('Upgrade to Pro to continue generating content.', 'phantomwriter'); ?>
					</p>
					<div class="phantomwriter-card-actions phantomwriter-justify-center">
						<a class="phantomwriter-btn phantomwriter-btn-wide phantomwriter-btn-accent phantomwriter-text-accent-content" href="<?php echo esc_url(PHANTOMWRITER_CLIENT_UPGRADE_LINK); ?>" target="_blank">
							<?php _e('Upgrade to Pro', 'phantomwriter'); ?>
						</a>
					</div>
				</div>
			</div>
		<?php endif; ?>
<?php
	}
}
