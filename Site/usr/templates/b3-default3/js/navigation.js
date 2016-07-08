/**
 * @package: SobiPro Component for Joomla!

 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: https://www.Sigsiu.NET

 * @copyright Copyright (C) 2006 - 2015 Sigsiu.NET GmbH (https://www.sigsiu.net). All rights reserved.
 * @license GNU/GPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3
 * as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/gpl.html and https://www.sigsiu.net/licenses.

 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 */

 SobiPro.jQuery( document ).ready( function ()
{
	if ( SobiPro.jQuery( '.ctrl-static-navigation' ).length ) {
		SobiPro.jQuery( '.ctrl-ajax-navigation' ).removeClass( 'hide' );
		SobiPro.jQuery( '.ctrl-static-navigation' ).addClass( 'hide' );
		var LastSite = SobiPro.jQuery( '.ctrl-static-navigation ul li' )
			.last()
			.find( 'a' )
			.attr( 'href' )
			.match( /site=([^&]+)/ )[1];
		SobiPro.jQuery( '.ctrl-ajax-navigation' ).click( function ()
		{
			var Site = parseInt( SobiPro.jQuery( '[name="currentSite"]' ).val() ) + 1;
			var Proxy = this;
			SobiPro.jQuery( Proxy )
				.find( 'i' )
				.removeClass( 'fa fa-refresh' )
				.addClass( 'fa fa-circle-o-notch fa-spin' );
			if ( Site == LastSite ) {
				SobiPro.jQuery( Proxy ).attr( 'disabled', 'disabled' );
			}
			SobiPro.jQuery
				.ajax( {
					url: document.URL,
					data: {'sptpl': 'section.ajax', 'tmpl': 'component', 'format': 'raw', 'xmlc': 1, 'site': Site},
					type: 'POST',
					dataType: 'html'
				} )
				.done( function ( data )
				{
					SobiPro.jQuery( Proxy ).before( data );
					SobiPro.jQuery( '[name="currentSite"]' ).val( Site );
					SobiPro.jQuery( Proxy )
						.find( 'i' )
						.removeClass( 'fa fa-circle-o-notch fa-spin' )
						.addClass( 'fa fa-refresh' );
				} );
		} );
	}
} );
