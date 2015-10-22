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
	SobiPro.jQuery( '#SPMenuCtrlBt' ).click( function ()
	{
		SPRightMenu();
	} );

	function SPRightMenu()
	{
		if ( SobiPro.jQuery( '#SPRightMenu' ).css( 'display' ) == 'block' ) {
			SobiPro.jQuery( '#SPRightMenu' ).hide( 'slide' );
			SobiPro.jQuery( '#SPRightMenuHold' ).hide();
			SobiPro.jQuery( '#SPRightMenuHold' ).html( SobiPro.jQuery( '#SPMenuCtrl' ).html() );
			SobiPro.jQuery( '#SPMenuCtrl' ).html( '' );
			SobiPro.jQuery( '#SPMenuCtrlBt' ).html( '+ menu' );
			SobiPro.jQuery( '#SPRightMenuHold' ).fadeIn( 'slide' );
			SobiPro.jQuery( '#SPRightMenu' )
				.siblings( 'div' )
				.removeClass( 'span10' )
				.addClass( 'span12' )
                .addClass('firstspan');
		}
		else {
			SobiPro.jQuery( '#SPRightMenu' ).show( 'slide' );
			SobiPro.jQuery( '#SPMenuCtrl' ).html( SobiPro.jQuery( '#SPRightMenuHold' ).html() );
			SobiPro.jQuery( '#SPRightMenuHold' ).html( '' );
			SobiPro.jQuery( '#SPMenuCtrlBt' ).html( '-' );
			SobiPro.jQuery( '#SPRightMenu' )
				.siblings( 'div' )
				.removeClass( 'span12' )
                .removeClass('firstspan')
				.addClass( 'span10' );
		}
		SobiPro.jQuery( '#SPMenuCtrlBt' ).click( function ()
		{
			SPRightMenu();
		} );
	}
} );
