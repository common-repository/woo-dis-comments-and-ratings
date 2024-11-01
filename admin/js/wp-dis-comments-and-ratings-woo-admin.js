/**
 * Plugin specific JQuery functionality
 *
 */

(function ($) {
	'use strict';
	var disqusComments = {
		paid_error: function (element) {
			if (element.parent().find(".zamartz-message.error").length === 0) {
				$('<span class="zamartz-message error">Error: Use with Paid Version Only</span>').appendTo(element.parent());
			}
		},
		coreHandler: function (zamartzMain) {
			$(document).ready(function () {
				$('.woo-placement-option-selector').each(function () {
					if ($(this).val() == 'custom') {
						var input_field = $(this).data('input_field');
						$('input[name=' + input_field + ']').closest('.woo-disqus-custom-css-indentifier').show();
					}
				});

				$('.zamartz-wrapper').on('select2:select', '.woo-placement-option-selector', function () {
					var input_field = $(this).data('input_field');
					if ($(this).val() == 'custom') {
						$('input[name=' + input_field + ']').closest('.woo-disqus-custom-css-indentifier').show();
					} else {
						$('input[name=' + input_field + ']').closest('.woo-disqus-custom-css-indentifier').hide();
						$('input[name=' + input_field + ']').val('');
						$('input[name=' + input_field + ']').parent().find('.zamartz-message').removeClass('error').html('');
					}
					if ($(this).closest('.zamartz-wrapper').hasClass('plugin-free-version')) {
						disqusComments.paid_error($(this));
					}
				});

				$('.woo-disqus-post-identifier-product').each(function () {
					if ($(this).val() == 'manual_entry') {
						var input_field = $(this).data('input_field');
						$('input[name=' + input_field + ']').closest('.woo-disqus-manual-post-identifier').show();
					}
				});
				
				$('.zamartz-wrapper').on('select2:select', '.woo-disqus-post-identifier-product', function () {
					var input_field = $(this).data('input_field');
					if ($(this).val() == 'manual_entry') {
						$('input[name=' + input_field + ']').closest('.woo-disqus-manual-post-identifier').show();
					} else {
						$('input[name=' + input_field + ']').closest('.woo-disqus-manual-post-identifier').hide();
						$('input[name=' + input_field + ']').val('');
					}
				});

				$('.zamartz-wrapper .woo-disqus-custom-css-indentifier').on('keyup', 'input', function () {
					if ($(this).closest('.zamartz-wrapper').hasClass('plugin-free-version')) {
						disqusComments.paid_error($(this));
					}
				});

				$('.zamartz-wrapper').on('click', '.zamartz-paid-feature', function (e) {
					e.preventDefault();
					disqusComments.paid_error($(this).closest('td').find('.additional-content span'));
				});

				$('.zamartz-wrapper').on('click', '.woo-disqus-auto-assign', function (e) {
					if ($(this).closest('.zamartz-wrapper').hasClass('plugin-free-version')) {
						e.preventDefault();
						disqusComments.paid_error($(this).parent());
						return;
					}
					//Paid feature
					var dashicon = $('.woo-disqus-auto-assign-dashicon');
					var settings_nonce = $('#zamartz_settings_nonce').val();
					var value = 'no';
					var zamartz_message = $(this).closest('td').find('.zamartz-message');

					dashicon.show();
					zamartz_message.removeClass('error success');
					zamartz_message.html('');

					if ($(this).is(':checked')) {
						value = 'yes'
					}

					$.ajax({
						url: zamartz_localized_object.ajax_url,
						type: 'POST',
						data: {
							action: 'woo_disqus_get_shortcode_ajax',
							settings_nonce: settings_nonce,
							value: value,
						}
					}).always(function (json) {
						//Hide spinner
						dashicon.hide();
						console.log(json);

						var response = jQuery.parseJSON(json);
						var message_class = 'success';
						if (response.status == false) {
							$('.woo-disqus-auto-assign').prop('checked', !$('.woo-disqus-auto-assign').is(':checked'));
							message_class = 'error';
						} else {
							var shortcode_input = $('input[name=woo_disqus_shortcode_value]');
							if (response.shortcode == '') {
								//Clear manual shortcode field
								shortcode_input.val('');
								//Remove read-only
								// shortcode_input.attr("readonly", false);
							} else {
								//Define manual shortcode field
								shortcode_input.val(response.shortcode);
								//Add read-only
								// shortcode_input.attr("readonly", true);
							}
						}
						zamartz_message.removeClass('error success');
						zamartz_message.addClass(message_class);
						zamartz_message.html(response.message);
					});

				});

				$('.zamartz-wrapper').on('change', '.disqus-post-identifier', function () {
					if ($(this).closest('.zamartz-wrapper').hasClass('plugin-free-version')) {
						disqusComments.paid_error($(this));
					}
				});

				if ($('#post_type').val() === 'product') {
					$('#post').on('submit', function (e) {
						$(this).find('.zamartz-checkbox').each(function () {
							if ($(this).is(':checked')) {
								$(this).parent().find('input[type=hidden]').val('yes');
							} else {
								$(this).parent().find('input[type=hidden]').val('no');
							}
						});
					});
				}
			});
		}
	};
	disqusComments.coreHandler(window.zamartzMain);
})(jQuery);