/**
 * @package: SobiPro Component for Joomla!

 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: https://www.Sigsiu.NET

 * @copyright Copyright (C) 2006 - 2015 Sigsiu.NET GmbH (https://www.sigsiu.net). All rights reserved.
 * @license GNU/GPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3
 * as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/gpl.html and https://www.sigsiu.net/licenses.

 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 */


SobiPro.jQuery( document ).ready( function ()
{
	SobiPro.jQuery( '.alpha-switch' ).bind( 'click', function ( e )
	{
		e.preventDefault();
		SobiPro.jQuery.ajax( {
			url:SobiProUrl.replace( '%task%', 'list.alpha.switch.' + SobiPro.jQuery( this ).attr( 'rel' ) ),
			data:{ sid:SobiProSection, tmpl:"component", format:"raw" },
			success:function ( jsonObj )
			{
				SobiPro.jQuery( '#alpha-index' ).html( jsonObj.index );
			}
		} );
	} );
	SobiPro.jQuery( '.dropdown-toggle' ).dropdown();
	try {
		SobiPro.jQuery( '#spDeleteEntry' ).click( function ( e )
		{
			"use strict";
			if ( !( confirm( SobiPro.Txt( 'CONFIRM_DELETE_ENTRY' ) ) ) ) {
				e.preventDefault();
			}
		} );
	}
	catch ( e ) {
	}

	try {
		SobiPro.jQuery( '#spCategoryContainer-hide' ).slideToggle( 'fast' );
		SobiPro.jQuery( '#spCategoryShow').attr('data-visible', false);

		SobiPro.jQuery( '#spCategoryShow' ).bind( 'click', function() {
			if (SobiPro.jQuery( '#spCategoryShow').attr('data-visible') == 'false') {
				SobiPro.jQuery( '#spCategoryShow').attr('data-showtext', SobiPro.jQuery( '#spCategoryShow').val()); //save the origin
				SobiPro.jQuery( '#spCategoryShow').val(SobiPro.jQuery('#hidetext').val());  //set new
				SobiPro.jQuery( '#spCategoryShow').attr('data-visible', true);
			}
			else {
				SobiPro.jQuery( '#spCategoryShow').attr('data-visible', false);
				SobiPro.jQuery( '#spCategoryShow').val(SobiPro.jQuery( '#spCategoryShow').attr('data-showtext'));
			}
			SobiPro.jQuery( '#spCategoryContainer-hide' ).slideToggle( 'fast' );
		} );
	} catch( e ) {}
} );

