/**
 * @version: $Id: menu.js 551 2011-01-11 14:34:26Z Radek Suski $
 * @package: SobiPro Library
 * ===================================================
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET
 * ===================================================
 * @copyright Copyright (C) 2006 - 2011 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license see http://www.gnu.org/licenses/lgpl.html GNU/LGPL Version 3.
 * You can use, redistribute this file and/or modify it under the terms of the GNU Lesser General Public License version 3
 * ===================================================
 * $Date: 2011-01-11 15:34:26 +0100 (Tue, 11 Jan 2011) $
 * $Revision: 551 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/js/adm/menu.js $
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
			SobiPro.jQuery( '#SPMenuCtrlBt' ).html( '+ menu' )
			SobiPro.jQuery( '#SPRightMenuHold' ).fadeIn( 'slide' );
			SobiPro.jQuery( '#SPRightMenu' )
				.siblings( 'div' )
				.removeClass( 'span10' )
				.addClass( 'span11' );
		}
		else {
			SobiPro.jQuery( '#SPRightMenu' ).show( 'slide' );
			SobiPro.jQuery( '#SPMenuCtrl' ).html( SobiPro.jQuery( '#SPRightMenuHold' ).html() );
			SobiPro.jQuery( '#SPRightMenuHold' ).html( '' )
			SobiPro.jQuery( '#SPMenuCtrlBt' ).html( '-' )
			SobiPro.jQuery( '#SPRightMenu' )
				.siblings( 'div' )
				.removeClass( 'span11' )
				.addClass( 'span10' );
		}
		SobiPro.jQuery( '#SPMenuCtrlBt' ).click( function ()
		{
			SPRightMenu();
		} );
	}
} );
