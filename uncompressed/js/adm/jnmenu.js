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
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 * or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 */

var SobiProSelectedUrl = '';
SobiPro.jQuery( document ).ready( function ()
{
	var modal = SobiPro.jQuery( '#SobiProModal' );
	SobiProSelectedUrl = SobiPro.jQuery( '#jform_link' ).val();
	SobiPro.jQuery( '#jform_link' ).css( 'min-width', '400px' );
	modal.addClass('spModalIframe narrow');
	modal.find( '.ctrl-save' ).click( function ( e )
	{
		e.preventDefault();
		var fields = modal.find( 'iframe' ).contents().find( ':input' );
		var setting = {};
		var request = [];
		SobiPro.jQuery.each( fields, function ( i, e )
		{
			e = SobiPro.jQuery( e );
			switch ( e.attr( 'name' ) ) {
				case 'sid':
					SobiPro.jQuery( '#selectedSid' ).val( e.val() );
					break;
				case 'function-name':
					var functionName = e.val();
					if ( functionName.indexOf( '%s' ) != -1 ) {
						functionName = functionName.replace( '%s', SobiPro.jQuery( "#SobiSection" ).find( ':selected' ).text() );
					}
					SobiPro.jQuery( '#SobiProSelectedFunction' ).html( functionName );
					break;
				default:
					if ( e.attr( 'name' ).indexOf( 'params' ) != -1 ) {
						var name = e.attr( 'name' ).replace( 'params[', '' ).replace( ']', '' );
						setting[ name ] = e.val();
					}
					else if ( e.attr( 'name' ).indexOf( 'request' ) != -1 ) {
						var name = e.attr( 'name' ).replace( 'request[', '' ).replace( ']', '' );
						request.push( { 'name': name, 'value': e.val() } );
					}
					break;
			}
		} );
		if ( request.length ) {
			var link = 'index.php?option=com_sobipro&';
			SobiPro.jQuery.each( request, function ( i, e )
			{
				if ( e.value.length ) {
					link = link + e.name + '=' + e.value;
					if ( i + 1 < request.length ) {
						link = link + '&';
					}
				}
			} );
			SobiProSelectedUrl = link;
			SPUpdateUrl();
		}
		SobiPro.jQuery( '.SobiProSettings' ).val( SobiPro.jQuery.base64.encode( JSON.stringify( setting ) ) );
	} );

	modal.find( '.ctrl-clear' ).click( function ( e )
	{
		SobiProSelectedUrl = '';
		SPUpdateUrl();
		SobiPro.jQuery( '#SobiProSelectedFunction' ).html( SpStrings.buttonLabel )
	} );

	SobiPro.jQuery( '#SobiProSelector' ).bind( 'click', function ( e )
	{
		if ( SobiPro.jQuery( "#SobiSection" ).val() == 0 || SobiPro.jQuery( "#SobiSection" ).val() == "" ) {
			SobiPro.Alert( 'YOU_HAVE_TO_AT_LEAST_SELECT_A_SECTION' );
			return false;
		}
		modal.modal();
		var mid = SobiPro.jQuery( '#SobiProSelector' ).data( 'mid' );
		var section = SobiPro.jQuery( '#SobiSection' ).val();
		modal.find( '.modal-body' )
			.html( '<iframe src="index.php?option=com_sobipro&section=' + section + '&task=menu&tmpl=component&format=html&mid=' + mid + '" class="spJMenu"> </iframe>' );
	} );

	try {
		var JSubmit = Joomla.submitbutton;
		Joomla.submitbutton = function ( pressbutton, type )
		{
			if ( pressbutton.indexOf( 'save' ).indexOf == -1 || pressbutton.indexOf( 'apply' ) == -1 || SPValidate() ) {
				JSubmit( pressbutton, type );
			}
		}
	}
	catch ( x ) {
		var JSubmit = submitbutton;
		submitbutton = function ( pressbutton, type )
		{
			if ( pressbutton.indexOf( 'save' ).indexOf == -1 || pressbutton.indexOf( 'apply' ) == -1 || SPValidate() ) {
				JSubmit( pressbutton, type );
			}
		}
	}
	SobiPro.jQuery( '#SobiProFunctions' ).change( function ()
	{
		if ( SobiPro.jQuery( this ).val() ) {
			SobiPro.jQuery( 'body' ).find( 'form' ).submit();
		}
	} );

	SobiPro.jQuery( '.SobiProCalendar' ).change( function ()
		{
			"use strict";
			var date = [];
			SobiPro.jQuery( '.SobiProCalendar' ).each( function ( i, e )
			{
				if ( SobiPro.jQuery( this ).val() ) {
					date.push( SobiPro.jQuery( this ).val() );
				}
			} );
			SobiPro.jQuery( '[name=request\\[date\\]]' ).val( date.join( '.' ) );
		}
	);

	SobiPro.jQuery( '.SobiProEntryChooser' ).typeahead( {
		source: function ( typeahead, query )
		{
			var request = {
				'option': 'com_sobipro',
				'task': 'entry.search',
				'sid': SobiPro.jQuery( '#SP_section' ).val(),
				'search': query,
				'format': 'raw'
			};
			return SobiPro.jQuery.ajax( {
				'type': 'post',
				'url': 'index.php',
				'data': request,
				'dataType': 'json',
				success: function ( response )
				{
					responseData = [];
					if ( response.length ) {
						for ( var i = 0; i < response.length; i++ ) {
							responseData[ i ] = { 'name': response[ i ].name + ' ( ' + response[ i ].id + ' )', 'id': response[ i ].id, 'title': response[ i ].name  };
						}
						typeahead.process( responseData );
					}
				}
			} );
		},
		onselect: function ( obj )
		{
			SobiPro.jQuery( '#SP_sid' ).val( obj.id );
			SobiPro.jQuery( '#selectedEntryName' ).val( obj.title );
			SobiPro.jQuery( 'input[name=function-name]' ).val( SobiPro.jQuery( '#entryName' ).html().replace( '%s', obj.title ) );
		},
		property: "name"
	} );
	try {
		SobiPro.jQuery( SobiPro.jQuery( '#jform_type-lbl' ).siblings()[ 0 ] ).val( SpStrings.component );
	}
	catch ( e ) {
	}
	try {
		var lType = SobiPro.jQuery( '[name*="jform[type]"]' ).parent().find( 'input[type=text]' );
		lType.val( SpStrings.component );
		lType.css( 'min-width', '200px' );
		SobiPro.jQuery( '#jform_link' ).css( 'min-width', '500px' );
	}
	catch ( e ) {
	}
} );

function SP_selectCat( sid )
{
	SobiPro.jQuery( 'input[name*=sid]' ).val( sid );
	SobiPro.jQuery( '#sobiCats_CatUrl' + sid ).focus();
	SobiPro.jQuery.ajax( {
		'url': 'index.php',
		'data': { 'task': 'category.parents', 'out': 'json', 'option': 'com_sobipro', 'format': 'raw', 'tmpl': 'component', 'sid': sid },
		'type': 'post',
		'dataType': 'json'
	} ).done(
		function ( response )
		{
			var catName = '';
			SobiPro.jQuery.each( response.categories, function ( i, cat )
			{
				catName = cat.name;
			} );
			SobiPro.jQuery( 'input[name=function-name]' ).val( SobiPro.jQuery( '#categoryName' ).html().replace( '%s', catName ) );
		}
	);
}

function SPUpdateUrl()
{
	if ( !( SobiPro.jQuery( '#selectedSid' ).val() ) ) {
		SobiPro.jQuery( '#selectedSid' ).val( SobiPro.jQuery( '#SobiSection' ).val() );
	}
	if ( SobiProSelectedUrl == '' ) {
		SobiProSelectedUrl = 'index.php?option=com_sobipro&sid=' + SobiPro.jQuery( '#selectedSid' ).val();
		/** Sat, Feb 7, 2015 18:16:22 - what the heck? */
		//SobiPro.jQuery( '.SobiProSettings' ).val( '' );
	}
	SobiPro.jQuery( '#jform_link' ).val( SobiProSelectedUrl );
}

function SPValidate()
{
	SPUpdateUrl();
	if ( SobiPro.jQuery( "#selectedSid" ).val() == 0 || SobiPro.jQuery( "#selectedSid" ).val() == "" ) {
		SobiPro.Alert( 'YOU_HAVE_TO_AT_LEAST_SELECT_A_SECTION' );
		return false;
	}
	else {
		return true;
	}
}
