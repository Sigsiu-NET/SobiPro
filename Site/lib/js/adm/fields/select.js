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
	SobiPro.jQuery( '#field-dependency' )
		.find( '[value="1"]' )
		.click( function ()
		{
			SobiPro.jQuery( this ).parent().parent().parent().parent().next().next().hide();
			SobiPro.jQuery( this ).parent().parent().parent().parent().next().show();
		} );
	SobiPro.jQuery( '#field-dependency' )
		.find( '[value="0"]' )
		.click( function ()
		{
			SobiPro.jQuery( this ).parent().parent().parent().parent().next().hide();
			SobiPro.jQuery( this ).parent().parent().parent().parent().next().next().show();
		} );
	if ( SobiPro.jQuery( '#field-dependency' ).find( '[value="1"]' ).hasClass( 'selected' ) ) {
		SobiPro.jQuery( '#field-dependency' ).find( '[value="1"]' ).parent().parent().parent().parent().next().next().hide();
		SobiPro.jQuery( '#field-dependency' ).find( '[value="1"]' ).parent().parent().parent().parent().next().show();
	}
	else {
		SobiPro.jQuery( '#field-dependency' ).find( '[value="0"]' ).parent().parent().parent().parent().next().hide();
		SobiPro.jQuery( '#field-dependency' ).find( '[value="0"]' ).parent().parent().parent().parent().next().next().show();
	}
} );
