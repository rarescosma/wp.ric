/// <reference path="doc/jquery-vsdoc.js" />

// @depends assets/ui.colorpicker.js
// @depends assets/ui.datepicker.js
// @depends assets/jquery.elastic.js
// @depends assets/jquery.jgrowl.js

$ = jQuery.noConflict();

var ricUI;

ricUI = {
	start: function () {

		this.colorPicker();
		this.elasticTextarea();
		this.countingTextarea();
		this.datePicker();
		this.image();
		this.MCE();
		this.optionPanelNav();
		this.optionPanelAJAX();
	},

	colorPicker: function () {
		// Get all colorpicker DIVs
		var $colorpickers = $('div.ric-ui-colorpicker');
		$colorpickers.each(function () {
			var $picker = $(this);
			var $input = $picker.next('input.ric-ui-colorpicker');
			var color = $input.attr('value');

			if (color != '') {
				$picker.children('div').css('backgroundColor', color);
				$picker.ColorPicker({
					color: color,
					onShow: function (the_picker) {
						jQuery(the_picker).fadeIn(500);
						return false;
					},
					onHide: function (the_picker) {
						jQuery(the_picker).fadeOut(500);
						return false;
					},
					onChange: function (hsb, hex, rgb) {

						$picker.children('div').css('backgroundColor', '#' + hex);
						$input.attr('value', '#' + hex);
					}
				});
			}
		});

		// Trigger click from the input element
		$('input.ric-ui-colorpicker').bind('click', function (e) {
			e.preventDefault();
			$(this).prev('div.ric-ui-colorpicker').trigger('click');
		});
	},

	elasticTextarea: function () {
		$textareas = $('.ric-ui textarea');
		if ($textareas.length > 0) {
			$textareas.elastic();
		}
	},

	countingTextarea: function () {
		$('.ric-ui-counting-textarea').bind('keyup keydown focus', ricUI.countingTextarea_event);
		$('.ric-ui-counting-textarea').live('input paste', ricUI.countingTextarea_event);
	},

	countingTextarea_event: function () {

		var maxLength = parseInt($(this).attr('maxlength'));
		var charLength = $(this).val().length;

		if (charLength > maxLength) {
			$(this).val($(this).val().substr(0, maxLength));
			return false;
		}

		var currentCount = maxLength - charLength;

		$(this).next('div.counter').children('p').html(currentCount);
	},

	datePicker: function () {
		$('.ric-ui-datepicker').datepicker();

		// Trigger click from the icon
		$('div.ric-ui-datepicker-controls > div.icon').bind('click', function () {
			$(this).next('input').trigger('focus');
		});
	},

	image: function () {
		window.ricui_id = false;

		// Trigger click from the icon
		$('div.ric-ui-image-controls > div.icon').bind('click', function (e) {
			window.ricui_id = $(this).next('input').attr('id');
			tb_show('Insert Image', 'media-upload.php?post_id=' + ricUI_options.post_id + '&amp;type=image&amp;TB_iframe=true');
			e.preventDefault();
		});

		// Media Upload Context
		if ( pagenow == 'media-upload-popup' ) {
			if( parent.ricui_id ) {
				// Clean up form
				$('body').addClass('ric-ui-media-upload');
			}
		}

		// Replace tb_remove
		window.original_tb_remove = window.tb_remove;

		window.tb_remove = function() {
			if (ricui_id ) {
				ricui_id = false;
			}
			window.original_tb_remove();
		}

		// Replace send_to_editor
		window.original_send_to_editor = window.send_to_editor;

		window.send_to_editor = function (html) {
			if( ricui_id ) {
				// Select the default image size
				$('div.image-size-item > input[value="full"]').click();
				// Get the URL
				var url = $(html).attr('src') || $(html).find('img').attr('src') || $(html).attr('href');
				$('#' + ricui_id).val(url);
				window.tb_remove();
			} else {
				window.original_send_to_editor(html);
			}
		}
	},

	MCE: function () {
		if (typeof (tinyMCE) == "object" && typeof (tinyMCE.execCommand) == "function") {
			$('.ric-ui-mce').each(function () {
				var ta = $(this);
				var id = $(this).attr('id');
				ricUI.MCE_go(id, 'tinymce');
				var hidden = false;

				$toggle_link = $(this).parent().next().find('a.toggle');

				$toggle_link.bind('click', function (e) {

					e.preventDefault();
					if ( hidden ) {
						ricUI.MCE_go(id, 'tinymce');
						hidden = false;
					} else {
						ed = tinyMCE.get(id);
						ed.hide();
						hidden = true;
					}
				});
			});
		}
	},

	MCE_go: function(id, mode) { // mode can be 'html', 'tmce', or 'toggle'
		id = id || 'content';
		mode = mode || 'toggle';

		var t = this, ed = tinyMCE.get(id), wrap_id, txtarea_el, dom = tinymce.DOM;

		wrap_id = 'wp-'+id+'-wrap';
		txtarea_el = dom.get(id);

		if ( 'tmce' == mode || 'tinymce' == mode ) {
			if ( ed && ! ed.isHidden() )
				return false;

			if( ! tinyMCEPreInit.mceInit[id] ) {
				tinyMCEPreInit.mceInit[id] = tinyMCEPreInit.mceInit['content1'];
			}

			if ( tinyMCEPreInit.mceInit[id] && tinyMCEPreInit.mceInit[id].wpautop )
				txtarea_el.value = switchEditors.wpautop( txtarea_el.value );

			if ( ed ) {
				ed.show();
			} else {
				ed = new tinymce.Editor(id, tinyMCEPreInit.mceInit[id]);
				ed.render();
			}

			dom.removeClass(wrap_id, 'html-active');
			dom.addClass(wrap_id, 'tmce-active');
		}
		return false;
	},

	optionPanelNav: function () {
		// Hide all groups but the first
		$groups = $('#ric-content .group');
		$groups.hide().filter(':first').fadeIn();

		// Activate first link
		$('#ric-nav li:first').addClass('current');

		$('#ric-nav li a').bind('click', function (e) {
			$('#ric-nav li').removeClass('current');
			$(this).parent().addClass('current');

			$groups.hide().filter($(this).attr('href')).fadeIn();

			e.preventDefault();
		});
	},

	optionPanelAJAX: function () {
		$('#ric-form :submit').click(function (e) {
			// Trigger MCE Save
			tinyMCE.triggerSave();

			// Get relevant elements
			var $submit = $(this);
			var $form = $submit.parents('form');
			var $spinner = $submit.prev('.ajax-loading-img');

			// Display the AJAX indicator and disable button
			$spinner.fadeIn('fast');
			$submit.attr('disabled', 'disabled');

			var data = $form.serializeArray();
			data.push({ name: 'action', value: 'ric_option_post' });

			$.post(ricUI_options.ajaxurl, data, function (response) {
				$.jGrowl(response, {
					position: 'bottom-right',
					sticky: 'true'
				});

				$spinner.fadeOut('fast');
				$submit.removeAttr('disabled');
			});

			e.stopPropagation();
			e.preventDefault();
		});
	}
};

$(document).ready(function () {
	ricUI.start();
});