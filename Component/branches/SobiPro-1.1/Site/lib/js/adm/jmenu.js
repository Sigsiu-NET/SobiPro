/**
 * @version: $Id: sobipro.js 551 2011-01-11 14:34:26Z Radek Suski $
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
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/js/adm/sobipro.js $
 */

SobiPro.jQuery( document ).ready( function () {
	SPJoomlaMenu();
} );

function SPJoomlaMenu()
{
	var semaphore = 0;
	var spApply = $$( "#toolbar-apply a" )[ 0 ];
	var spSave = $$( "#toolbar-save a" )[ 0 ];
	spApplyFn = spApply.onclick;
	spApply.onclick = null;
	spSaveFn = spSave.onclick;
	spSave.onclick = null;
	try {
		var spSaveNew = $$( "#toolbar-save-new a" )[ 0 ];
		spSaveNewFn = spSaveNew.onclick;
		spSaveNew.onclick = null;
		spSaveNew.addEvent( "click", function ()
		{
			if ( SPValidate() ) {
				spSaveNewFn();
			}
		} );
	}
	catch ( e ) {
	}

	spApply.addEvent( "click", function ()
	{
		if ( SPValidate() ) {
			spApplyFn();
		}
	} );

	spSave.addEvent( "click", function ()
	{
		if ( SPValidate() ) {
			spSaveFn();
		}
	} );

	$( "spsection" ).addEvent( "change", function ( event )
	{
		sid = $( "spsection" ).options[ $( "spsection" ).selectedIndex ].value;
		$( "sid" ).value = sid;
		semaphore = 0;
	} );

	if ( $( "sp_category" ) != null ) {
		$( "sp_category" ).addEvent( "click", function ( ev )
		{
			if ( semaphore == 1 ) {
				return false;
			}
			semaphore = 1;
			new Event( ev ).stop();
			if ( $( "sid" ).value == 0 ) {
				SobiPro.Alert( "JS_PLEASE_SELECT_SECTION_FIRST" );
				semaphore = 0;
				return false;
			}
			else {
				url = SobiProUrl.replace( '%task%', 'category.chooser' ) + '&treetpl=rchooser&tmpl=component&sid=' + $( "sid" ).value;
				try {
					SqueezeBox.open( $( "sp_category" ), { handler:"iframe", size:{ x:700, y:500 }, url:url } );
				}
				catch ( x ) {
					SqueezeBox.fromElement( $( "sp_category" ), { url:url, handler:"iframe", size:{ x:700, y:500 } } );
				}
			}
		} );
	}
}

function SPValidate()
{
	if ( $( "sid" ).value == 0 || $( "sid" ).value == "" ) {
		SobiPro.Alert( 'JS_YOU_HAVE_TO_AT_LEAST_SELECT_A_SECTION' )
		return false;
	}
	else {
		return true;
	}
}
