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
	SobiPro.jQuery( '.spCountableReset' ).click( function ( e )
	{
		if ( confirm( SobiPro.Txt( 'CONFIRM_URL_COUNT_RESET' ) ) ) {
			var sid = SobiPro.jQuery( '#SP_sid' ).val();
			var fid = SobiPro.jQuery( this ).attr( 'name' ).replace( '_reset', '' ).replace( 'field_', 'field.' );
			if ( fid && sid ) {
				SobiPro.jQuery.ajax( { 'url':'index.php', 'data':{ 'sid':SobiProSection, 'task':fid + '.reset', 'eid':sid, 'option':'com_sobipro', 'format':'raw' }, 'type':'post', 'dataType':'json' } );
				SobiPro.jQuery( this ).html( SobiPro.jQuery( this ).html().replace( /\d{1,}/, 0 ) );
				SobiPro.jQuery( this ).attr( 'disabled', 'disabled' );
			}
		}
	} );
} );
