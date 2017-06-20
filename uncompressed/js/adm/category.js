/**
 * @package: SobiPro Library
 *
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: https://www.Sigsiu.NET
 *
 * @copyright Copyright (C) 2006 - 2017 Sigsiu.NET GmbH (https://www.sigsiu.net). All rights reserved.
 * @license GNU/LGPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License version 3
 * as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/lgpl.html and https://www.sigsiu.net/licenses.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 */

SobiPro.jQuery(document).ready(function () {
	SobiPro.jQuery('.spCategoryChooser').click(function () {
		var requestUrl = SobiPro.jQuery(this).attr('rel');
		SobiPro.jQuery( '#spCatsChooser' ).html( '<iframe id="spCatSelectFrame" src="' + requestUrl + '"> </iframe>' );
		SobiPro.jQuery('#spCat').modal();
	});

	SobiPro.jQuery('#spCatSelect').bind('click', function (e) {
		if (!( SobiPro.jQuery('#SP_selectedCid').val() )) {
			return;
		}
		SobiPro.jQuery('#selectedCatPath').html(SobiPro.jQuery('#SP_selectedCatPath').val());
		SobiPro.jQuery('[name^="category.parent"]').val(SobiPro.jQuery('#SP_selectedCid').val());
		SobiPro.jQuery('#categoryParentName').html(SobiPro.jQuery('#SP_selectedCatName').val());
	});

	if (SobiPro.jQuery('#SP_categoryIconHolder').val()) {
		SobiPro.jQuery( '#catIcoChooser' ).html( '<img src="' + SobiPro.jQuery( '#SP_categoryIconHolder' ).val() + '" class="spCatImage" />' );
	}

	if (SobiPro.jQuery('[name^="category.icon"]').val() && SobiPro.jQuery('[name^="category.icon"]').val().indexOf('font-') != -1) {
		SobiPro.jQuery('#catIcoChooser').addClass('el5');
		var Icon = JSON.parse(SobiPro.jQuery('[name^="category.icon"]').val().replace(/\\'/g, '"'));
		var Content = ( Icon.content != undefined ) ? Icon.content : '';
		SobiPro.jQuery('#catIcoChooser').html('<' + Icon.element + ' class="spIconElement ' + Icon.class + '">' + Content + '</' + Icon.element + '>');
	}

	// show additional input field for fonts
	if (SobiPro.jQuery('#category-params-icon-font').val() && SobiPro.jQuery('#category-params-icon-font').val().indexOf('font-') != -1) {
		SobiPro.jQuery('.ctrl-add-font-class').removeClass('hide');
	}

	SobiPro.jQuery.ajax({
		'url': 'index.php',
		'type': 'post',
		'dataType': 'json',
		'data': {
			'option': 'com_sobipro',
			'task': 'category.iconFonts',
			'sid': SobiProSection,
			'format': 'raw',
			'tmpl': 'component',
			'method': 'xhr'
		}
	}).done(function (response) {
		SobiPro.jQuery('#category-params-icon-font option').each(function (i, e) {
			var Found = false;
			var Option = SobiPro.jQuery(this).val();
			if (Option != 0) {
				SobiPro.jQuery.each(response, function (i, e) {
					if (Option.indexOf(e) != -1) {
						Found = true;
					}
				});
				if (!(Found)) {
					SobiPro.jQuery(this).attr('disabled', 'disabled');
				}
			}
		});
	});

	SobiPro.jQuery('#category-params-icon-font').change(function () {
		if (SobiPro.jQuery(this).val() && SobiPro.jQuery(this).val().indexOf('font-') != -1) {
			SobiPro.jQuery('.ctrl-add-font-class').removeClass('hide');
			SobiPro.jQuery('#spIco').addClass('spModalPopup');
			SobiPro.jQuery('#spIco').removeClass('spModalIframe');
		}
		else {
			SobiPro.jQuery('.ctrl-add-font-class').addClass('hide');
			SobiPro.jQuery('#spIco').addClass('spModalIframe');
			SobiPro.jQuery('#spIco').removeClass('spModalPopup');
		}
	});

	SobiPro.jQuery('#catIcoChooser').click(function () {
		if (SobiPro.jQuery('#category-params-icon-font').val().indexOf('font-') != -1) {
			SobiPro.jQuery('#spIcoChooser').html('');
			var Request = {
				'option': 'com_sobipro',
				'task': 'category.icon',
				'sid': SobiProSection,
				'format': 'raw',
				'tmpl': 'component',
				'method': 'xhr',
				'font': SobiPro.jQuery('#category-params-icon-font').val()
			};
			SobiPro.jQuery.ajax({
				url: 'index.php',
				type: 'post',
				dataType: 'json',
				data: Request
			}).done(function (response) {
				if (response.length) {
					var Element;
					var Size = 1;
					try {
						Size = SobiPro.jQuery('#category-params-icon-font option:selected').text().match(/\((.*?)\)/);
						Size = parseInt(Size[1]);
					}
					catch (e) {
						Size = 1;
					}
					SobiPro.jQuery.each(response, function (i, e) {
						var Content = ( e.content != undefined ) ? e.content : '';
						Element = e.element;
						e.font = SobiPro.jQuery('#category-params-icon-font').val();
						SobiPro.jQuery('#spIcoChooser')
							.append('<div class="spIconElCont el' + Size + '">' +
								'<' + e.element + ' class="spIconElement ' + e.class + '" data-setting="' + JSON.stringify(e).replace(/"/g, "'") + '">' +
								Content +
								'</' + e.element + '></div>');
					});
					SobiPro.jQuery('#spIcoChooser').find(Element).click(function () {
						SobiPro.jQuery('#catIcoChooser').html('');
						SobiPro.jQuery('#catIcoChooser').append(SobiPro.jQuery(this).clone());
						SobiPro.jQuery('[name^="category.icon"]').val(SobiPro.jQuery(this).data('setting'));
						SobiPro.jQuery('#catIcoChooser').removeClass('el1');
						SobiPro.jQuery('#catIcoChooser').removeClass('el2');
						SobiPro.jQuery('#catIcoChooser').removeClass('el3');
						SobiPro.jQuery('#catIcoChooser').removeClass('el4');
						SobiPro.jQuery('#catIcoChooser').removeClass('el5');
						SobiPro.jQuery('#catIcoChooser').addClass('el' + Size);
						SobiPro.jQuery('.spIconElCont').removeClass('active');
						SobiPro.jQuery(this).parent().addClass('active');
					});
					SobiPro.jQuery('#spIco').addClass('spModalPopup');
					SobiPro.jQuery('#spIco').removeClass('spModalIframe');
					SobiPro.jQuery('#spIco').modal();
				}
			});
		}
		else {
			var requestUrl = SobiPro.jQuery(this).attr('rel');
			SobiPro.jQuery( '#spIcoChooser' ).html( '<iframe id="spIcoSelectFrame" src="' + requestUrl + '"> </iframe>' );
			SobiPro.jQuery('#spIco').addClass('spModalIframe');
			SobiPro.jQuery('#spIco').removeClass('spModalPopup');
			SobiPro.jQuery('#catIcoChooser').removeClass('el1');
			SobiPro.jQuery('#catIcoChooser').removeClass('el2');
			SobiPro.jQuery('#catIcoChooser').removeClass('el3');
			SobiPro.jQuery('#catIcoChooser').removeClass('el4');
			SobiPro.jQuery('#catIcoChooser').removeClass('el5');
			SobiPro.jQuery('#spIco').modal();
		}
	});
});

function SPSelectIcon(src, name) {
	SobiPro.jQuery('#SP_categoryIconHolder').val(src);
	SobiPro.jQuery('[name^="category.icon"]').val(name);
	if (SobiPro.jQuery('#SP_categoryIconHolder').val()) {
		SobiPro.jQuery( '#catIcoChooser' ).html( '<img src="' + SobiPro.jQuery( '#SP_categoryIconHolder' ).val() + '" class="spCatImage" />' );
	}
	SobiPro.jQuery('#spIco').modal('hide');
}
