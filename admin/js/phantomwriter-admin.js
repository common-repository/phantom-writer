(function ($) {
  "use strict";

	const { __ } = wp.i18n;

	function phantomWriterClientEditExcerpt(content, override) {
		if (phantomWriterClientIsEmpty(content)) return;

		const excerpt = wp.data.select( 'core/editor' ).getEditedPostAttribute( 'excerpt' );

		if (!phantomWriterClientIsEmpty(excerpt) && !override) return;

		wp.data.dispatch( 'core/editor' ).editPost( { excerpt: content } );
	}

  function phantomWriterClientEditTitleBlock(content, override) {
		if (phantomWriterClientIsEmpty(content)) return;

		const title = wp.data.select( 'core/editor' ).getEditedPostAttribute( 'title' );

		if (!phantomWriterClientIsEmpty(title) && ! override) return;

    wp.data.dispatch( 'core/editor' ).editPost( { title: content } );
  }

  function phantomWriterClientAddParagraphBlock(content) {
    const insertedBlock = wp.blocks.createBlock('core/paragraph', {
        content: content,
    });
    wp.data.dispatch( 'core/block-editor' ).insertBlocks(insertedBlock);
  }

	function phantomWriterClientGetNoticeIcon(type) {
		switch (type) {
			case 'error':
				return '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />';
			case 'success':
				return '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />';
			case 'warning':
				return '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />';
			default:
				return '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
		}
	}

	function phantomWriterParseNoticeMessage(message) {
		if (phantomWriterClientIsEmpty(message)) return '';
		if ('object' === typeof message) return JSON.stringify(message);

		return message;
	}

  function phantomWriterClientAddNotice(message, type) {
		phantomWriterClientRemoveNotice();

		var $className;
		switch (type) {
			case 'error':
				$className = "phantomwriter-alert-error";
				break;
			case 'success':
				$className = "phantomwriter-alert-success";
				break;
			case 'warning':
				$className = "phantomwriter-alert-warning";
				break;
			default:
				$className = "phantomwriter-alert-info";
		}

		const $notice_icon = $("#phantomwriter-notice svg");
		const $icon = phantomWriterClientGetNoticeIcon(type);
		if (phantomWriterClientIsEmpty($notice_icon)) return;

		$notice_icon.html($icon);
		const $notice = $("#phantomwriter-notice");
		if (phantomWriterClientIsEmpty($notice)) return;

		$notice.addClass($className);
		message = phantomWriterParseNoticeMessage(message);
		const $notice_message = $("#phantomwriter-notice span");
		$notice_message.text(message);

		const $notice_container = $('#phantomwriter-tabs-notice');
		if (phantomWriterClientIsEmpty($notice_container)) return;
		$notice_container.removeClass('phantomwriter-hidden');
  }

  function phantomWriterClientRemoveNotice() {
		const $notice_container = $('#phantomwriter-tabs-notice');
		if (!phantomWriterClientIsEmpty($notice_container)) $notice_container.addClass('phantomwriter-hidden');

		const $notice = $("#phantomwriter-notice");
		if (phantomWriterClientIsEmpty($notice)) return;
		$notice.removeClass("phantomwriter-alert-error");
		$notice.removeClass("phantomwriter-alert-success");
		$notice.removeClass("phantomwriter-alert-warning");
		$notice.removeClass("phantomwriter-alert-info");
  }

  function phantomWriterClientRemoveParagraphBlocks() {
    const blocks = wp.data.select( 'core/block-editor' ).getBlocks();
    blocks.forEach((block) => {
      if (block.name === 'core/paragraph') {
        wp.data.dispatch( 'core/block-editor' ).removeBlock(block.clientId);
      }
    });
  }

	function phantomWriterClientSetLoading(state) {
		const $loading = $('#phantomwriter-loading-modal');

		if (state) {
			$loading.addClass('phantomwriter-modal-open');
			$('#phantomwriter-tabs-btn').addClass('phantomwriter-hidden');
			$('#phantomwriter-tabs').addClass('phantomwriter-hidden');
			$('#phantomwriter-tabs-loading').addClass('phantomwriter-flex')
			$('#phantomwriter-tabs-loading').removeClass('phantomwriter-hidden');
		} else {
			$loading.removeClass('phantomwriter-modal-open');
			$('#phantomwriter-tabs-btn').removeClass('phantomwriter-hidden');
			$('#phantomwriter-tabs').removeClass('phantomwriter-hidden');
			$('#phantomwriter-tabs-loading').removeClass('phantomwriter-flex')
			$('#phantomwriter-tabs-loading').addClass('phantomwriter-hidden');
		}
	}

	function phantomWriterClientIsEmpty(value) {
		return value === null || value === undefined || value === '' ||
			(Array.isArray(value) && value.length === 0) ||
			(typeof value === 'object' && Object.keys(value).length === 0);
	}

	function phantomWriterClientCheckOverride(id) {
		const $el = $(id);

		if (phantomWriterClientIsEmpty($el)) return false;

		if ($el.is('select')) {
			return $el.val();
		}

		throw new Error('Invalid element type');
	}

	function phantomWriterClientSetBtnLoading(btn, state) {
		if (phantomWriterClientIsEmpty(btn)) return;

		if (state) {
			btn.html('<span class="phantomwriter-loading"></span> ' + btn.text());
			btn.attr("disabled", true);
		} else {
			btn.html(btn.text().replace('<span class="phantomwriter-loading"></span> ', ''));
			btn.attr("disabled", false);
		}
	}

	function phantomWriterClientProcessResultsModelAction($button, $type) {
		$button = $($button);
		if (phantomWriterClientIsEmpty($button)) return;
		if (phantomWriterClientIsEmpty($type)) return;

		phantomWriterClientSetBtnLoading($button, true);
		const $postID = $button.data("post-id");
		if (phantomWriterClientIsEmpty($postID)) {
			phantomWriterClientSetBtnLoading($button, false);
			phantomWriterClientAddNotice(__('Invalid post ID', 'phantomwriter'), 'error');
			return
		}

		$.ajax({
			url: phantomwriter.ajax_url,
			type: "POST",
			data: {
				action: "phantomwriter_client_result_modal_action",
				nonce: phantomwriter.nonce,
				postID: $postID,
				type: $type,
			},
			success: function (response) {
				const $success = response.success;
				const $data    = response.data;

				if (!$success) {
					phantomWriterClientAddNotice(data, 'error');
					phantomWriterClientSetBtnLoading($(this), false);
					return
				};

				const $content = $data.content ?? '';

				if (phantomWriterClientIsEmpty($content)) return phantomWriterClientAddNotice(__('Content is empty', 'phantomwriter'), 'error');

				switch ($type.toLowerCase()) {
					case 'title':
						phantomWriterClientEditTitleBlock($content, true);
						break;
					case 'description':
					case 'content':
						const $paragraphs = $content.split(/\n\n|\n/);

						if (! $paragraphs || 0 === $paragraphs.length) {
							phantomWriterClientAddParagraphBlock($content);
						} else {
							$paragraphs.forEach(($paragraph) => {
								$paragraph = $paragraph.trim();

								if ('' === $paragraph || ! $paragraph) {
									return;
								}

								phantomWriterClientAddParagraphBlock($paragraph);
							});
						}
						break;
					case 'prompt':
						const $promptInput = $("#phantomwriter-prompt");
						$promptInput.val($content);
						$("#phantomwriter-tab-prompt-btn").trigger("click");
						break;
					default:
						phantomWriterClientAddNotice(__('Invalid type selected', 'phantomwriter'), 'error');
						console.error('Invalid type selected: ', $type);
				}
			},
			error: function (error) {
				phantomWriterClientAddNotice(error, 'error');
			},
			complete: function () {
				if (!phantomWriterClientIsEmpty($button)) phantomWriterClientSetBtnLoading($button, false);
			}
		});
	}


	function phantomWriterClientToggleTab(tabId) {
		$(".phantomwriter-js-tab").addClass("phantomwriter-hidden");
		$("#" + tabId).removeClass("phantomwriter-hidden");
	}

	function phantomWriterClientToggleTabLinkActive(link) {
		$("#phantomwriter-tabs-btn a").removeClass("phantomwriter-tab-active");
		link.addClass("phantomwriter-tab-active");
	}

	function phantomWriterClientUpdateTable(postID, language, prompt, title, content, wordsCount) {
		const $table = $('#phantomwriter-table');

		if (phantomWriterClientIsEmpty($table)) return;

		const $tbody = $table.find('tbody');
		if (phantomWriterClientIsEmpty($tbody)) return;

		const $tr = $('<tr></tr>');
		const $tdPostID = $('<td class="phantomwriter-mx-auto phantomwriter-text-center"></td>');
		const $tdLanguage = $('<td></td>');
		const $tdPrompt = $('<td></td>');
		const $tdTitle = $('<td></td>');
		const $tdContent = $('<td></td>');
		const $tdWordsCount = $('<td class="phantomwriter-mx-auto phantomwriter-text-center"></td>');

		$tdPostID.text(postID);
		$tdLanguage.text(language ?? 'English');
		$tdPrompt.text(prompt);
		$tdTitle.text(title);
		$tdContent.text(content);
		$tdWordsCount.text(wordsCount);

		$tr.append('<th>0</th>');
		$tr.append($tdPostID);
		$tr.append($tdLanguage);
		$tr.append($tdPrompt);
		$tr.append($tdTitle);
		$tr.append($tdContent);
		$tr.append($tdWordsCount);
		$tr.append(
			`<th>
				<div class="phantomwriter-join phantomwriter-join-vertical phantomwriter-gap-2 phantomwriter-rounded-none">
					<button class="phantomwriter-btn phantomwriter-btn-primary phantomwriter-join-item phantomwriter-rounded-md" disabled>
						${__('Add Title', 'phantomwriter')}
					</button>
					<button class="phantomwriter-btn phantomwriter-btn-secondary phantomwriter-join-item phantomwriter-rounded-md" disabled>
						${__('Add Content', 'phantomwriter')}
					</button>
				</div>
			</th>`
		);
		$tbody.prepend($tr);

		const $trs = $tbody.find('tr');
		if (phantomWriterClientIsEmpty($trs)) return;

		$trs.each(function (index) {
			$(this).find('th:first-child').text(index + 1);
		});
	}

	function phantomWriterClientProcessRequest() {
		phantomWriterClientRemoveNotice();
		const $promptInput = $("#phantomwriter-prompt");
		const $promptValue = $promptInput.val().trim();
		const $submitBtn   = $("#phantomwriter-button");

		if (phantomWriterClientIsEmpty($promptValue)) {
			phantomWriterClientAddNotice(
				__('Please enter a prompt', 'phantomwriter'),
				'error'
			);
			if (!phantomWriterClientIsEmpty($submitBtn)) phantomWriterClientSetBtnLoading($submitBtn, false);
			return;
		}

		const $lengthInput = $("#phantomwriter-length");
		const $lengthValue = parseInt($lengthInput.val().trim()) || 100;

		if (isNaN($lengthValue)) {
			phantomWriterClientAddNotice( __('Length must be a number', 'phantomwriter'), 'error' );
			if (!phantomWriterClientIsEmpty($submitBtn)) phantomWriterClientSetBtnLoading($submitBtn, false);
			return;
		}

		if ($lengthValue < 100) {
			phantomWriterClientAddNotice( __('Length must be at least 100', 'phantomwriter'), 'error' );
			if (!phantomWriterClientIsEmpty($submitBtn)) phantomWriterClientSetBtnLoading($submitBtn, false);
			return;
		}

		const $overrideSeoTitle    = phantomWriterClientCheckOverride("#phantomwriter-override-seo-title");
		const $overrideSeoDesc     = phantomWriterClientCheckOverride("#phantomwriter-override-seo-description");
		const $overrideSeoKeywords = phantomWriterClientCheckOverride("#phantomwriter-override-seo-keywords");
		const $overrideTitle       = phantomWriterClientCheckOverride("#phantomwriter-override-title");
		const $overrideContent     = phantomWriterClientCheckOverride("#phantomwriter-override-content");
		const $overrideExcerpt     = phantomWriterClientCheckOverride("#phantomwriter-override-excerpt");
		const $generateImageIdea   = phantomWriterClientCheckOverride("#phantomwriter-generate-image-idea");

		const $language_code = $("#phantomwriter-language option:selected").val();

		phantomWriterClientSetLoading(true);

		$.ajax({
			url: phantomwriter.ajax_url,
			type: "POST",
			data: {
				action: "phantomwriter_client_get_content",
				nonce: phantomwriter.nonce,
				prompt: $promptValue,
				length: $lengthValue,
				languageCode: $language_code,
				overrideSeoTitle: $overrideSeoTitle,
				overrideSeoDesc: $overrideSeoDesc,
				overrideSeoKeywords: $overrideSeoKeywords,
				overrideTitle: $overrideTitle,
				overrideContent: $overrideContent,
				overrideExcerpt: $overrideExcerpt,
				generateImageIdea: $generateImageIdea,
			},
			success: function (response) {
				const success = response.success;
				const data    = response.data;

				if (!success) return phantomWriterClientAddNotice( data, 'error' );

				const resultPostID   = data.result_post_id;
				const language       = data.language ?? 'English';
				const title          = data.title && data.title.length > 0 ? data.title.replace(/^"(.+(?="$))"$/, "$1") : '';
				const content        = data.content && data.content.length > 0 ? data.content.trim() : '';
				const excerpt        = data.excerpt && data.excerpt.length > 0 ? data.excerpt.trim() : '';
				const postWords      = data.post_words;
				const remainingWords = data.remaining_words;
				const redirect			 = data.redirect ?? false;

				if (!phantomWriterClientIsEmpty(redirect)) return window.location.replace(redirect);

				const $shouldOverrideTitle = $overrideTitle === 'override' ? true : false;
				phantomWriterClientEditTitleBlock(title, $shouldOverrideTitle);

				if (content) {
					const paragraphs = content.split(/\n\n|\n/);

					if ($overrideContent === 'override') {
						phantomWriterClientRemoveParagraphBlocks();
					}

					if (! paragraphs || 0 === paragraphs.length) {
						phantomWriterClientAddParagraphBlock(content);
					} else {
						paragraphs.forEach((paragraph) => {
							paragraph = paragraph.trim();

							if ('' === paragraph || ! paragraph) {
								return;
							}

							phantomWriterClientAddParagraphBlock(paragraph);
						});
					}
				}

				const $shouldOverrideExcerpt = $overrideExcerpt === 'override' ? true : false;
				phantomWriterClientEditExcerpt(excerpt, $shouldOverrideExcerpt);

				if (!phantomWriterClientIsEmpty(resultPostID)) phantomWriterClientUpdateTable(resultPostID, language, $promptValue, title, content, postWords);

				$('#phantomwriter-words-count').text(remainingWords);
				phantomWriterClientAddNotice( __('Information retrieved successfully', 'phantomwriter'), 'success' );
			},
			error: function (error) {
				phantomWriterClientAddNotice(error, 'error');
			},
			complete: function () {
				phantomWriterClientSetLoading(false);
				const $submitBtn = $("#phantomwriter-button");
				if (!phantomWriterClientIsEmpty($submitBtn)) phantomWriterClientSetBtnLoading($submitBtn, false);
			}
		});
	}

	function phantomWriterClientToggleTabAndLink(tabId, link) {
		phantomWriterClientToggleTab(tabId);
		phantomWriterClientToggleTabLinkActive(link);
	}

  $(document).ready(function () {
		$('#phantomwriter-copy-image-idea').click(function() {
			var imageIdeaContent = $('#phantomwriter-image-idea').val();
			var $temp = $("<input>");
			$("body").append($temp);
			$temp.val(imageIdeaContent).select();
			document.execCommand("copy");
			$temp.remove();
		});

    $("#phantomwriter-tabs-btn a").on("click", function (event) {
			event.preventDefault();
			const $tab = $(this).data("tab");

			phantomWriterClientToggleTabAndLink($tab, $(this));
    });

    $("#phantomwriter-button").on("click", function (event) {
      event.preventDefault();
			phantomWriterClientSetBtnLoading($(this), true);
			phantomWriterClientProcessRequest();
    });

		$(".phantomwriter-js-results-add-prompt").on("click", function (event) {
			event.preventDefault();
			phantomWriterClientProcessResultsModelAction($(this), 'prompt');
		});

		$(".phantomwriter-js-results-add-title").on("click", function (event) {
			event.preventDefault();
			phantomWriterClientProcessResultsModelAction($(this), 'title');
		});

		$(".phantomwriter-js-results-add-content").on("click", function (event) {
			event.preventDefault();
			phantomWriterClientProcessResultsModelAction($(this), 'content');
		});
  });

})(jQuery);
