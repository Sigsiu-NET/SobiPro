/**
 * @version: $Id$
 * @package: SobiPro Library

 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET

 * @copyright Copyright (C) 2006 - 2015 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license GNU/LGPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License version 3 as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/lgpl.html and http://sobipro.sigsiu.net/licenses.

 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

 * $Date$
 * $Revision$
 * $Author$
 * $HeadURL$
 */

SobiPro.jQuery( document ).ready( function ()
{
	function SpAddDependencyListener( e )
	{
		var Proxy = SobiPro.jQuery( e );
		var Fid = Proxy.attr( 'id' ).replace( '_', '.' );
		var Canvas = Proxy.parent().parent();
		var Path = Canvas.find( 'input:hidden' );
		var Selected = {};
		var LastList;
		if ( Path.val().length ) {
			Selected = JSON.parse( Path.val() )
		}
		SobiPro.jQuery.each( Selected, function ( el )
		{
			LastList = el;
		} );
		if ( Proxy.data( 'order' ) < LastList ) {
			SobiPro.jQuery.each( Canvas.find( '[name="' + Proxy.attr( 'id' ) + '"]' ), function ( i, el )
			{
				if ( SobiPro.jQuery( el ).data( 'order' ) > Proxy.data( 'order' ) ) {
					SobiPro.jQuery( el ).detach();
					delete Selected[SobiPro.jQuery( el ).data( 'order' )];
				}
			} );
		}
		Selected[Proxy.data( 'order' )] = Proxy.val();
		Path.val( JSON.stringify( Selected ) );
		SobiPro.jQuery.ajax( {
			'url': 'index.php',
			'data': {
				'sid': SobiProSection,
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
			if ( SobiPro.jQuery.makeArray( response.options ).length ) {
				var SelectList = Proxy.clone();
				SelectList.find( 'option' ).remove();
				SobiPro.jQuery.each( response.options, function ( label, id )
				{
					SelectList.append( new Option( id, label ) );
				} );
				SelectList.data( 'order', parseInt( SelectList.data( 'order' ) ) + 1 )
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
