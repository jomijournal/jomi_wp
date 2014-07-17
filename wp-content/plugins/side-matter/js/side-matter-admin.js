/*
side-matter-admin.js
Version 1.4

Plugin: Side Matter
Author: Christopher Setzer
URI: http://wordpress.org/extend/plugins/side-matter/
License: GPLv2
*/

(function($) {

/* Variables */

var fieldIsDark; // True when `div.side-matter-preview` has class `side-matter-preview-dark` applied
var colorsEnabled; // True when 'Use custom colors for figures and notes' checkbox is checked
var figureColor; // Current color to use for reference and note numeral figures
var textColor; // Current color to use for note text

var figures = { // Numeral style figures for preview field
	'armenian': 'Ա',
	'decimal': '1',
	'georgian': 'ა',
	'hebrew': 'א',
	'hiragana': 'あ',
	'hiragana-iroha': 'い',
	'katakana': 'ア',
	'katakana-iroha': 'イ',
	'lower-alpha': 'a',
	'lower-greek': 'α',
	'lower-roman': 'i',
	'none': ''
};

$(document).ready(function() { // Assign initial values, position note in preview field, and load color pickers

	if ($('.side-matter-use-effects').prop('checked')) $('.side-matter-preview-list').css('opacity', 0);

	fieldIsDark = 0;
	figureColor = $('.side-matter-figure-color').val();
	textColor = $('.side-matter-text-color').val();

	$('.side-matter-figure-color').wpColorPicker(figureOptions);
	$('.side-matter-text-color').wpColorPicker(textOptions);

	if ($('.side-matter-colors-enabled').prop('checked')) {
		$('.side-matter-preview-ref,.side-matter-preview-note').css('color', figureColor);
		$('.side-matter-preview-text').css('color', textColor);
	} else {
		$('.side-matter-user-colors').hide();
	}

});

$(window).load(function() {
	for (i = 1; i <= 2; i++) placeNote();
	if ($('.side-matter-use-effects').prop('checked')) $('.side-matter-preview-list').fadeTo(360, 1);
});

function placeNote() { // Position note in preview field (variant of `placeNotes` from `side-matter.js`)
	var refPosition = $('.side-matter-preview-ref').position().top;
	var notePosition = $('.side-matter-preview-note').position().top;
	var noteOffset = refPosition - notePosition;
	var finalOffset = (noteOffset < 0) ? 0 : noteOffset;
	$('.side-matter-preview-note').css('marginTop', finalOffset); // Position note
}

/* Parameters for updating preview field text colors when color picker is changed */

var figureOptions = {
	change: function(event, ui) {
		$('.side-matter-preview-ref,.side-matter-preview-note').css('color', ui.color.toString());
	},
	palettes: false
};

var textOptions = {
	change: function(event, ui) {
		$('.side-matter-preview-text').css('color', ui.color.toString());
	},
	palettes: false
};

/* Functions for live updates to preview field: toggling light/dark, enabling custom colors, updating figure style, fade effect */

$('.side-matter-preview,label[for^="side-matter-preview"]').click(function() { // When user clicks preview field or its label, toggle class
	if (fieldIsDark == 0) {
		$('.side-matter-preview').addClass('side-matter-preview-dark');
		fieldIsDark = 1;
	} else {
		$('.side-matter-preview').removeClass('side-matter-preview-dark');
		fieldIsDark = 0;
	}
});

$('.side-matter-preview').keyup(function(e) { // Enter and space keys will toggle preview field when it is in focus
	if (e.which == 13 || e.which == 32) $('.side-matter-preview').click();
});

$('.side-matter-colors-enabled').click(function() { // When user clicks checkbox, open color picker drawer
	if ($(this).prop('checked')) {
		$('.side-matter-preview-ref,.side-matter-preview-note').css('color', figureColor);
		$('.side-matter-preview-text').css('color', textColor);
		$('.side-matter-user-colors').slideDown();
	} else {
		figureColor = $('.side-matter-figure-color').val();
		textColor  = $('.side-matter-text-color').val();
		$('.side-matter-user-colors').slideUp('fast');
		$('.side-matter-preview-ref,.side-matter-preview-note,.side-matter-preview-text').css('color', '');
	}
});

$('.side-matter-figure-style').change(function() { // When user selects a figure style, update preview field
	$('.side-matter-preview-sup').text(figures[$(this).val()]);
	$('.side-matter-preview-note').css('listStyleType', $(this).val());
	placeNote();
});

$('.side-matter-use-effects').change(function() { // When user enables fade effects, update preview field
	if ($(this).prop('checked')) {
		$('.side-matter-preview-note').css('opacity', 0).delay(180).fadeTo(360, 1);
	}
});

$('label[for^="side-matter-pages-active"]').click(function() {
	if ($('.side-matter-pages-active-front').prop('checked') && $('.side-matter-pages-active-home').prop('checked') && $('.side-matter-pages-active-page').prop('checked') && $('.side-matter-pages-active-post').prop('checked')) {
		$('.side-matter-pages-active-front,.side-matter-pages-active-home,.side-matter-pages-active-post,.side-matter-pages-active-page').attr('checked', false);
	} else {
		$('.side-matter-pages-active-front,.side-matter-pages-active-home,.side-matter-pages-active-post,.side-matter-pages-active-page').attr('checked', true);
	}
});

})(jQuery);
