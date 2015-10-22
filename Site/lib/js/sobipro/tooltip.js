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

/**
 * To change this template use File | Settings | File Templates.
 */

SobiPro.jQuery( document ).ready( function ()
{
	var template = '<div class="popover"><div class="arrow"></div><div class="popover-inner"><div class="pull-right close spclose">x</div><h3 class="popover-title"></h3><div class="popover-content"><p></p></div></div></div>';
	SobiPro.jQuery( '[data-toggle=popover]' )
		.popover( { 'html': true, 'trigger': 'click', 'placement': 'top', 'template': template } )
		.click( function ( e )
		{
			e.preventDefault();
			var proxy = SobiPro.jQuery( this );
			SobiPro.jQuery( this ).parent().find( '.popover' ).find( '.close' ).click( function ()
			{
				proxy.popover( 'hide' );
			} )
		} );
} );

