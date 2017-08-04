/**
 * @package: SobiPro Component for Joomla!

 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: https://www.Sigsiu.NET

 * @copyright Copyright (C) 2006 - 2016 Sigsiu.NET GmbH (https://www.sigsiu.net). All rights reserved.
 * @license GNU/GPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3
 * as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/gpl.html and https://www.sigsiu.net/licenses.

 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 */

SobiPro.jQuery( document ).ready( function ()
{
	// initialize tooltips
	SobiPro.jQuery('[data-toggle="tooltip"]').tooltip();

	// initialize carousel slider
	SobiPro.jQuery( '#spCarousel' ).carousel();

	SobiPro.jQuery('.nav-pills li a').click(function (e) {
		e.preventDefault();
		SobiPro.jQuery(this).tab('show');
	});
	SobiPro.jQuery('.nav-tabs li a').click(function (e) {
		e.preventDefault();
		SobiPro.jQuery(this).tab('show');
	});
	if (window.location.hash) {
		SobiPro.jQuery('.nav-pills a[href="' + window.location.hash + '"]').tab('show');
		SobiPro.jQuery('.nav-tabs a[href="' + window.location.hash + '"]').tab('show');
	}

	//resize the map in entry form, necessary if the map is in a tab
	SobiPro.jQuery( 'a[href="#location"]' ).on( 'shown.bs.tab', function ( e )
	{
		SobiPro.jQuery( window ).trigger( 'resize' );
		try {
			var handler = SPGeoMapsReg[ jQuery( 'div[id^=field_map_canvas_]' ).attr( 'id' ) ];
			handler.Map.setCenter( handler.Position );
		}
		catch ( e ) {
		}

	} );

	//resize the map, necessary if the map is in a collapsable element
	SobiPro.jQuery( 'a[href="#location"]' ).on( 'shown.bs.collapse', function ( e )
	{
		SobiPro.jQuery( window ).trigger( 'resize' );
		try {
			var handler = SPGeoMapsReg[ jQuery( 'div[id^=field_map_canvas_]' ).attr( 'id' ) ];
			handler.Map.setCenter( handler.Position );
		}
		catch ( e ) {
		}
	} );
} );
