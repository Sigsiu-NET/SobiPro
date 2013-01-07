/**
 * @version: $Id: requirements.js 1377 2011-05-19 16:19:16Z Sigrid Suski $
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
 * $Date: 2011-05-19 18:19:16 +0200 (Thu, 19 May 2011) $
 * $Revision: 1377 $
 * $Author: Sigrid Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/js/adm/requirements.js $
 */

var SPStartTime = 0;
var SPErrors = 0;
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
		if ( SPErrors && (( new Date().getTime() - SPStartTime ) < 3000 ) ) {
			alert( SobiPro.Txt( 'REQUIREMENT_READ_PLEASE' ).replace( '%d', Math.ceil( ( new Date().getTime() - SPStartTime ) / 1000 ) ) );
			SPStartTime = 0;
		}
		else {
			document.location = SobiPro.jQuery( '#SP_redirect' ).val();
		}
		return false;
	} );
} );

function SobiProRequirements()
{
	"use strict";
	SPStartTime = new Date().getTime();
	SobiPro.jQuery( '&nbsp;<span id="SpProgress"></span>' )
		.appendTo( SobiPro.jQuery( '#SobiPro' ).find( '.alert' ) );
	this.elements = SobiPro.jQuery( '.spOutput' );
	this.spinner = '<img src="' + SPLiveSite + 'media/sobipro/adm/spinner.gif"/>';
	this.running = this.elements.length;
	var def = SobiPro.jQuery( '#SP_method' ).next( 'input' );
	this.request = {
		'option':'com_sobipro',
		'method':'xhr',
		'format':'raw'
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
			SobiPro.jQuery( '#SpProgress' ).html( SobiPro.Txt( 'Done' ) )
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
			'type':'post',
			'url':'index.php',
			'data':request,
			'dataType':'json',
			success:function ( response )
			{
				if ( response.type == 'success' ) {
					SPErrors++
				}
				if ( response.type == 'error' ) {
					response.type = 'important';
				}
				var icons = {
					'important':'thumbs-down',
					'success':'thumbs-up',
					'warning':'hand-right'
				};
				el.html( '<span class="label label-' + response.type + '">&nbsp;<i class="icon-' + icons[ response.type ] + '"></i>&nbsp;&nbsp;' + response.textType + '</span>&nbsp;&nbsp;' + response.message );
				proxy.running--;
				proxy.setMessage();
			},
			error:function ( xhr, textStatus )
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
