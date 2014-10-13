/**
 * @version: $Id$
 * @package: SobiPro Library

 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET

 * @copyright Copyright (C) 2006 - 2013 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
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
	SpCcSwapMethod( SobiPro.jQuery( '#field-method' ).val() );
	SobiPro.jQuery( '#field-method' ).change( function ()
	{
		SpCcSwapMethod( SobiPro.jQuery( this ).val() );
	} );
	function SpCcSwapMethod( method )
	{
		SobiPro.jQuery( '.spCcMethod' ).hide();
		SobiPro.jQuery( '.spCcMethod :input' ).attr( 'disabled', 'disabled' );
		SobiPro.jQuery( '#spCc-' + method + ' :input' ).removeAttr( 'disabled', 'disabled' );
		SobiPro.jQuery( '#spCc-' + method ).show();
		if ( method == 'fixed' ) {
			SobiPro.jQuery( '#field-editable :button' ).attr( 'disabled', 'disabled' );
			SobiPro.jQuery( '#field-editlimit' ).attr( 'disabled', 'disabled' );
		}
		else {
			SobiPro.jQuery( '#field-editable :button' ).removeAttr( 'disabled', 'disabled' );
			SobiPro.jQuery( '#field-editlimit' ).removeAttr( 'disabled', 'disabled' );
		}
	}
} );
