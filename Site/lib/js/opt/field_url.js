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
	SobiPro.jQuery( '.ctrl-visit-countable' ).click( function ( e )
	{
		var fid = '';
		var sid = SobiPro.jQuery( this ).data( 'sid' );
		SobiPro.jQuery.each( SobiPro.jQuery( this ).attr( 'class' ).split( ' ' ), function ( i, c )
		{
			if ( c.indexOf( 'field_' ) != -1 ) {
				fid = c.replace( 'field_', 'field.' );
			}
		} );
		if ( fid != '' && sid ) {
			SobiPro.jQuery.ajax( { 'url': 'index.php', 'data': { 'sid': SobiProSection, 'task': fid + '.count', 'eid': sid, 'option': 'com_sobipro', 'format': 'raw', 'tmpl': 'component' }, 'type': 'post', 'dataType': 'json' } );
		}
	} );

	SobiPro.jQuery.each( SobiPro.jQuery( '.ctrl-visit-countable' ), function ( i, el )
	{
		var e = SobiPro.jQuery( el );
		if ( e.data( 'refresh' ) ) {
			var fid = '';
			var sid = e.data( 'sid' );
			SobiPro.jQuery.each( e.attr( 'class' ).split( ' ' ), function ( i, c )
			{
				if ( c.indexOf( 'field_' ) != -1 ) {
					fid = c.replace( 'field_', 'field.' );
				}
			} );
			if ( fid != '' && sid ) {
				SobiPro.jQuery.ajax( { 'url': 'index.php', 'data': { 'sid': SobiProSection, 'task': fid + '.hits', 'eid': sid, 'option': 'com_sobipro', 'format': 'raw', 'tmpl': 'component' }, 'type': 'post', 'dataType': 'json' } ).
					done( function ( response )
					{
						var current = ' ' + e.data( 'counter' ) + ' ';
						var c = new String( current ).replace( /[0-9]/, response );
						e.html( e.html().replace( current, c ))
					} );
			}
		}
	} );
} );
