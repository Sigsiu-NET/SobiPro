/**
 * @version: $Id$
 * @package: SobiPro Library

 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET

 * @copyright Copyright (C) 2006 - 2014 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license GNU/LGPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License version 3 as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/lgpl.html and http://sobipro.sigsiu.net/licenses.

 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

 * $Date$
 * $Revision$
 * $Author$
 * $HeadURL$
 */

var SPStartTime = 0;
var SPErrors = 0;
var SPWarnings = 0;
SobiPro.jQuery().ready( function ()
{
	"use strict";
	new SobiProRequirements();
	SobiPro.jQuery( '#SobiPro' ).find( '.repeat' ).click( function ()
	{
		SobiPro.jQuery( '#SobiPro' ).find( '.bar' ).css( 'width', '1%' )
		setTimeout( function ()
		{
			new SobiProRequirements();
		}, 1000 );
	} );
	SobiPro.jQuery( '#SobiPro' ).find( '.download' ).click( function ()
	{
		document.location = SobiProAdmUrl.replace( '%task%', 'requirements.download' ) + '&format=raw';
	} );
	SobiPro.jQuery( '#SobiPro' ).find( '.next' ).click( function ()
	{
		if ( (SPWarnings || SPErrors) && (( new Date().getTime() - SPStartTime ) < 3000 ) ) {
			alert( SobiPro.Txt( 'REQUIREMENT_READ_PLEASE' ).replace( '%d', Math.ceil( ( new Date().getTime() - SPStartTime ) / 1000 ) ) );
			SPStartTime = 0;
		}
		else {
			try {
				window.top.location.href = SobiPro.jQuery( '#SP_redirect' ).val();
			}
			catch ( e ) {
			}
			document.location = SobiPro.jQuery( '#SP_redirect' ).val();
		}
		return false;
	} );
} );

function SobiProRequirements()
{
	"use strict";
	SPStartTime = new Date().getTime();
	SobiPro.jQuery( ' <span id="SpProgress"></span>' )
		.appendTo( SobiPro.jQuery( '#SobiPro' ).find( '.alert' ) );
	this.elements = SobiPro.jQuery( '.spOutput' );
	this.spinner = '<img src="' + SPLiveSite + 'media/sobipro/adm/spinner.gif"/>';
	this.running = this.elements.length;
	var def = SobiPro.jQuery( '#SP_method' ).next( 'input' );
	this.request = {
		'option': 'com_sobipro',
		'method': 'xhr',
		'format': 'raw'
	}
	this.request[ def.attr( 'name' ) ] = def.val();
	SobiPro.jQuery( '.buttons' ).addClass( 'hide' );
	var proxy = this;
	this.setMessage = function ()
	{
		if ( this.running ) {
			SobiPro.jQuery( '#SpProgress' )
				.html( SobiPro.Txt( 'REQUIREMENT_WORKING_MSG' ).replace( '%d', this.running ) );
		}
		else {
			SobiPro.jQuery( '.buttons' ).removeClass( 'hide' );
			SobiPro.jQuery( '#SpProgress' ).html( SobiPro.Txt( 'Done' ) );
			if ( SPErrors ) {
				SobiPro.Alert( 'REQUIREMENT_ERR' );
			}
			else if ( SPWarnings ) {
				SobiPro.Alert( 'REQUIREMENT_WARN' );
				SPStartTime = new Date().getTime();
			}
		}
		SobiPro.jQuery( '#SobiPro' ).find( '.bar' ).css( 'width', 100 / this.running + '%' )
	}
	this.setMessage();
	this.getResponse = function ( element, index )
	{
		var el = SobiPro.jQuery( element );
		var request = proxy.request;
		request[ 'task' ] = 'requirements.' + el.attr( 'id' );
		el.html( this.spinner );
		SobiPro.jQuery.ajax( {
			'type': 'post',
			'url': 'index.php',
			'data': request,
			'dataType': 'json',
			success: function ( response )
			{
				if ( response.type == 'warning' ) {
					SPWarnings++
				}
				if ( response.type == 'error' ) {
					response.type = 'important';
					SPErrors++
				}
				var icons = {
					'important': 'thumbs-down-alt',
					'success': 'thumbs-up-alt',
					'warning': 'hand-right'
				};
				el.html( '<span class="label label-' + response.type + '">&nbsp;<i class="icon-' + icons[ response.type ] + '"></i>&nbsp;&nbsp;' + response.textType + '</span>&nbsp;&nbsp;' + response.message );
				proxy.running--;
				proxy.setMessage();
			},
			error: function ( xhr, textStatus )
			{
				if ( textStatus == 'timeout' ) {
					if ( index < 20 ) {
						el.html( SobiPro.Txt( 'Too long answer' ) );
						proxy.getResponse( element, index++ );
					}
					else {
						el.html( SobiPro.Txt( 'Too long answer. Limit expired. Skipping' ) );
					}
				}
			}
		} );
	}
	this.elements.each( function ( index, element )
	{
		proxy.getResponse( element, 0 );
	} );
}
