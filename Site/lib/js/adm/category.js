/**
 * @version: $Id$
 * @package: SobiPro Library

 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET

 * @copyright Copyright (C) 2006 - 2015 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license GNU/LGPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License version 3
 * as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/lgpl.html and http://sobipro.sigsiu.net/licenses.

 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

 * $Date$
 * $Revision$
 * $Author$
 * $HeadURL$
 */

SobiPro.jQuery( document ).ready( function ()
{
	SobiPro.jQuery( '.spCategoryChooser' ).click( function ()
	{
		var requestUrl = SobiPro.jQuery( this ).attr( 'rel' );
		SobiPro.jQuery( '#spCatsChooser' ).html( '<iframe id="spCatSelectFrame" src="' + requestUrl + '" style="width: 100%; height: 100%; border: none;"> </iframe>' );
		SobiPro.jQuery( '#spCat' ).modal();
	} );
	SobiPro.jQuery( '#spCatSelect' ).bind( 'click', function ( e )
	{
		if ( !( SobiPro.jQuery( '#SP_selectedCid' ).val() ) ) {
			return;
		}
		SobiPro.jQuery( '#selectedCatPath' ).html( SobiPro.jQuery( '#SP_selectedCatPath' ).val() );
		SobiPro.jQuery( '[name^="category.parent"]' ).val( SobiPro.jQuery( '#SP_selectedCid' ).val() );
		SobiPro.jQuery( '#categoryParentName' ).html( SobiPro.jQuery( '#SP_selectedCatName' ).val() );
	} );
	if ( SobiPro.jQuery( '#SP_categoryIconHolder' ).val() ) {
		SobiPro.jQuery( '#catIcoChooser' ).html( '<img src="' + SobiPro.jQuery( '#SP_categoryIconHolder' ).val() + '" style="max-width: 55px; max-height: 55px;" />' );
	}
	if ( SobiPro.jQuery( '[name^="category.icon"]' ).val() ) {
		var Icon = JSON.parse( SobiPro.jQuery( '[name^="category.icon"]' ).val().replace( /\\'/g, '"' ) );
		var Content = ( Icon.content != undefined ) ? Icon.content : '';
		SobiPro.jQuery( '#catIcoChooser' ).html( '<' + Icon.element + ' style="margin: 5px; padding: 5px; cursor: pointer;" class="' + Icon.class + '"">' + Content + '</' + Icon.element + '>' );
	}
	SobiPro.jQuery( '#catIcoChooser' ).click( function ()
	{
		if ( SobiPro.jQuery( '#category-params-icon-font' ).val().indexOf( 'font-' ) != -1 ) {
			SobiPro.jQuery( '#spIcoChooser' ).html( '' );
			var Request = {
				'option': 'com_sobipro',
				'task': 'category.icon',
				'sid': SobiProSection,
				'format': 'raw',
				'tmpl': 'component',
				'method': 'xhr',
				'font': SobiPro.jQuery( '#category-params-icon-font' ).val()
			};
			SobiPro.jQuery.ajax( {
				url: 'index.php',
				type: 'post',
				dataType: 'json',
				data: Request
			} ).done( function ( response )
			{
				if ( response.length ) {
					var Element;
					SobiPro.jQuery.each( response, function ( i, e )
					{
						var Content = ( e.content != undefined ) ? e.content : '';
						Element = e.element;
						e.font = SobiPro.jQuery( '#category-params-icon-font' ).val();
						SobiPro.jQuery( '#spIcoChooser' )
							.append( '<' + e.element + ' style="margin: 5px; padding: 5px; cursor: pointer;" class="' + e.class + '" data-setting="' + JSON.stringify( e ).replace( /"/g, "'" ) + '">' + Content + '</' + e.element + '>' );
					} );
					SobiPro.jQuery( '#spIcoChooser' ).find( Element ).click( function ()
					{
						SobiPro.jQuery( '#catIcoChooser' ).html( '' );
						SobiPro.jQuery( '#catIcoChooser' ).append( SobiPro.jQuery( this ).clone() );
						SobiPro.jQuery( '[name^="category.icon"]' ).val( SobiPro.jQuery( this ).data( 'setting' ) )
					} );
					SobiPro.jQuery( '#spIco' ).modal();
				}
			} );
		}
		else {
			var requestUrl = SobiPro.jQuery( this ).attr( 'rel' );
			SobiPro.jQuery( '#spIcoChooser' ).html( '<iframe id="spIcoSelectFrame" src="' + requestUrl + '" style="height: 400px; border: none;"> </iframe>' );
			SobiPro.jQuery( '#spIco' ).modal();
		}
	} );
} );

function SPSelectIcon( src, name )
{
	SobiPro.jQuery( '#SP_categoryIconHolder' ).val( src );
	SobiPro.jQuery( '[name^="category.icon"]' ).val( name );
	if ( SobiPro.jQuery( '#SP_categoryIconHolder' ).val() ) {
		SobiPro.jQuery( '#catIcoChooser' ).html( '<img src="' + SobiPro.jQuery( '#SP_categoryIconHolder' ).val() + '" style="max-width: 55px; max-height: 55px;" />' );
	}
	SobiPro.jQuery( '#spIco' ).modal( 'hide' );
}
