/**
 * @package: SobiPro Library

 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: https://www.Sigsiu.NET

 * @copyright Copyright (C) 2006 - 2015 Sigsiu.NET GmbH (https://www.sigsiu.net). All rights reserved.
 * @license GNU/LGPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License version 3
 * as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/lgpl.html and https://www.sigsiu.net/licenses.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 */

SobiPro.jQuery( document ).ready( function ()
{
	function SpAddDependencyListener( e )
	{
		var Proxy = SobiPro.jQuery( e );
		var Fid = Proxy.attr( 'name' ).replace( '_', '.' );
		var Canvas = Proxy.parent().parent();
		var Path = Canvas.find( 'input:hidden' );
		var Selected = {};
		var LastList;
		var Spinner;
		if ( Path.val().length ) {
			Selected = JSON.parse( Path.val().replace( /\'/g, '"' ) );
		}
		SobiPro.jQuery.each( Selected, function ( el )
		{
			LastList = el;
		} );
		SobiPro.jQuery.each( Canvas.find( '[name="' + Proxy.attr( 'name' ) + '"]' ), function ( i, el )
		{
			if ( SobiPro.jQuery( el ).attr( 'data-order' ) > Proxy.attr( 'data-order' ) ) {
				SobiPro.jQuery( el ).detach();
				delete Selected[SobiPro.jQuery( el ).data( 'order' )];
			}
		} );
		Selected[Proxy.attr( 'data-order' )] = Proxy.val();
		Path.val( JSON.stringify( Selected ) );
		Spinner = SobiPro.jQuery( '<i class="' + SobiPro.Ico( 'select-field.spinner', 'icon-spinner icon-spin icon-large' ) + '"></i>' );
		Spinner.insertAfter( Proxy );
		Proxy.css( 'opacity', 0.5 );
		SobiPro.jQuery.ajax( {
			'url': SPLiveSite + 'index.php',
			'data': {
				'sid': Path.attr( 'data-section' ),
				'parent': Proxy.val(),
				'path': Path.val(),
				'task': Fid + '.dependency',
				'option': 'com_sobipro',
				'format': 'raw',
				'tmpl': 'component'
			},
			'type': 'post',
			'dataType': 'json'
		} ).done( function ( response )
		{
			Path.val( response.path );
			Spinner.detach();
			Proxy.css( 'opacity', 1 );
			if ( SobiPro.jQuery.makeArray( response.options ).length ) {
				var SelectList = Proxy.clone();
				SelectList.find( 'option' ).remove();
				SobiPro.jQuery.each( response.options, function ( label, id )
				{
					SelectList.append( new Option( id, label ) );
				} );
				var Current = parseInt( SelectList.attr( 'data-order' ) ) + 1;
				SelectList.attr( 'data-order', Current );
				SelectList.insertAfter( Proxy );
				SelectList.change( function ()
				{
					SpAddDependencyListener( this );
				} );
			}
		} );
	}

	SobiPro.jQuery( '.ctrl-dependency-field' ).change( function ()
	{
		SpAddDependencyListener( this );
	} );
} );
