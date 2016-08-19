/**
 * @version: $Id$
 * @package: SobiPro Library

 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET

 * @copyright Copyright (C) 2006 - 2015 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license GNU/LGPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License version 3 as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/lgpl.html and https://www.sigsiu.net/licenses.

 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

 * $Date$
 * $Revision$
 * $Author$
 * $HeadURL$
 */

SobiPro.jQuery().ready( function ()
{
	SobiPro.jQuery( '#SPAdminForm' ).on( 'BeforeAjaxSubmit', function ( e, handler, task )
	{
		if ( task == 'crawler.init' || task == 'crawler.restart' ) {
			handler.takeOver = true;
			new SobiProCrawler( task );
		}
	} );
} );

function SobiProCrawler( task )
{
	var proxy = this;
	this.message = SobiPro.jQuery( '#progressMessage' );
	this.spinner = '<i class="icon-refresh icon-spin"></i>&nbsp;';
	SobiPro.jQuery( '#crawlerResponse' ).find( '.invalidate' ).remove();
	this.row = SobiPro.jQuery( '#crawlerResponse' ).find( 'tbody' ).find( 'tr' );
	SobiPro.jQuery( '#crawlerResponse' ).find( 'tbody' ).find( 'tr' ).addClass( 'hide' );
	this.request = {
		'option':'com_sobipro',
		'format':'raw',
		'task':task,
		'sid':SobiProSection
	};
	this.setMessage = function ( message, spinner )
	{
		if ( spinner ) {
			this.message.html( this.spinner + message )
		}
		else {
			this.message.html( message )
		}
	};

	this.setMessage( SobiPro.Txt( 'PROGRESS_WORKING' ), true );
	SobiPro.jQuery( '#crawlerResponse' ).removeClass( 'hide' );
	this.parseResponse = function ( data )
	{
		SobiPro.jQuery.each( data, function ( i, element )
		{
			switch ( element.code ) {
				case 200:
					code = '<span class="label label-success">' + element.code + '</span>';
					break;
				case 412:
					code = '<span class="label label-inverse">' + element.code + '</span>';
					break;
				case 501:
					code = '<span class="label label-important">' + element.code + '</span>';
					break;
				default:
					code = '<span class="label label-warning">' + element.code + '</span>';
					break;
			}
			var row = proxy.row.clone();
			row.find( '.url' ).html( element.url );
			row.find( '.code' ).html( code );
			row.find( '.links' ).html( element.count );
			row.find( '.time' ).html( element.time );
			row.removeClass( 'hide' );
			row.addClass( 'invalidate' );
			SobiPro.jQuery( '#crawlerResponse' ).find( 'tbody' ).prepend( row );
		} );
	};

	this.getResponse = function ()
	{
		SobiPro.jQuery.ajax( {
			'type':'post',
			'url':SPLiveSite + '/index.php',
			'data':proxy.request,
			'dataType':'json',
			success:function ( response )
			{
				proxy.request[ 'task'] = 'crawler';
				if ( response.data.length ) {
					proxy.setMessage( response.message, true );
					proxy.parseResponse( response.data );
				}
				if ( response.status != 'done' ) {
					proxy.getResponse();
				}
				else {
					proxy.request[ 'task'] = 'crawler.init';
					proxy.setMessage( response.message, false );
				}
			}
		} );
	};
	this.getResponse();
}
