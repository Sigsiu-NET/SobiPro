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

SobiPro.jQuery( document ).ready( function ()
{
	SobiPro.jQuery( '.ctrl-entry-reject' ).SobiProRejections();
} );

SobiPro.jQuery.fn.SobiProRejections = function ()
{
	var proxy = this;
	this.Templates;
	this.header;
	this.RemoveButton = false;
	this.Sid;
	var modal = SobiPro.jQuery( '#reject-entry-window' );

	this.click( function ( e )
	{
		e.preventDefault();
		modal.modal();
		if ( modal.find( 'select' ).children( 'option' ).length == 0 ) {
			proxy.GetTemplates();
		}
		if ( !( proxy.header) ) {
			proxy.header = modal.find( '.modal-header' ).find( 'h3' ).html();
		}
		proxy.Sid = SobiPro.jQuery( this ).parent().parent().find( '[name="e_sid[]"]' ).val();
		modal.find( '.modal-header' ).find( 'h3' ).html( proxy.header + ' - ' + SobiPro.jQuery( this ).parent().parent().find( '.entry-name' ).find( 'a' ).html() );
	} );

	this.GetTemplates = function ()
	{
		modal.find( 'select' )
			.after( '<span class="spinner-remove">&nbsp;<i class="icon-spinner icon-spin"></i></span>' );
		modal.find( 'select' ).find( 'option' ).remove().end();
		var request = {
			'option': 'com_sobipro',
			'task': 'config.rejectionTemplates',
			'sid': SobiProSection,
			'format': 'raw',
			'tmpl': 'component',
			'method': 'xhr'
		};
		SobiPro.jQuery.ajax( {
			url: 'index.php',
			type: 'post',
			dataType: 'json',
			data: request
		} ).done( function ( response )
			{
				proxy.Templates = response;
				SobiPro.jQuery.each( response, function ( i, e )
				{
					modal.find( 'select' ).append( new Option( e.value, i ) );
				} );
				if ( !( proxy.RemoveButton ) ) {
					proxy.RemoveButton = true;
					modal.find( 'select' )
						.after( '&nbsp;&nbsp;<a class="btn btn-small btn-danger ctrl-remove-tpl" "><i class="icon-trash"></i></a>' );
					SobiPro.jQuery( '.ctrl-remove-tpl' ).click( function ()
					{
						proxy.RemoveTemplate();
					} )
				}
				proxy.GetTemplate();
				modal.find( '.spinner-remove' ).detach();
			} );
	};

	this.RemoveTemplate = function ()
	{
		var id = modal.find( 'select' ).find( ':selected' ).val();
		if ( id && confirm( SobiPro.Txt( 'ENTRY_REJECT_TEMPLATE_DELETE' ) ) ) {
			var request = {
				'option': 'com_sobipro',
				'task': 'config.deleteRejectionTemplate',
				'sid': SobiProSection,
				'tid': id,
				'format': 'raw',
				'tmpl': 'component',
				'method': 'xhr'
			};
			SobiPro.jQuery( modal.find( ':input' ) ).each( function ( i, b )
			{
				var bt = SobiPro.jQuery( b );
				request[ bt.attr( 'name' ) ] = bt.val();
			} );
			SobiPro.jQuery.ajax( {
				url: 'index.php',
				type: 'post',
				dataType: 'json',
				data: request
			} ).done( function ( response )
				{
					alert( response.message.text );
					proxy.GetTemplates();
				} );
		}
	};
	this.GetTemplate = function ()
	{
		var id = modal.find( 'select' ).find( ':selected' ).val();
		if ( id && this.Templates[ id ] ) {
			modal.find( '[name="reason"]' ).val( this.Templates[ id ].description );
			modal.find( '[name="trigger.unpublish"]' ).button( 'toggle' );
			SobiPro.jQuery.each( this.Templates[ id ].params, function ( i, e )
			{
				if ( e ) {
					modal.find( '[name="' + i + '"][value="1"]' ).addClass( 'btn-success active' );
					modal.find( '[name="' + i + '"][value="0"]' ).removeClass( 'btn-danger active selected' );
				}
				else {
					modal.find( '[name="' + i + '"][value="0"]' ).addClass( 'btn-danger active' );
					modal.find( '[name="' + i + '"][value="1"]' ).removeClass( 'btn-success active selected' );
				}
			} );
		}
	};

	modal.find( 'select' ).change( function ()
	{
		proxy.GetTemplate();
	} );

	modal.find( '.ctrl-reject' ).click( function ( e )
	{
		e.preventDefault();
		var request = {
			'option': 'com_sobipro',
			'task': 'entry.reject',
			'sid': proxy.Sid,
			'format': 'raw',
			'tmpl': 'component',
			'method': 'xhr'
		};
		SobiPro.jQuery( this ).html( SobiPro.jQuery( this ).html() + '<span class="spinner-remove">&nbsp;<i class="icon-spinner icon-spin"></i></span>' );
		SobiPro.jQuery( modal.find( ':input' ) ).each( function ( i, b )
		{
			var bt = SobiPro.jQuery( b );
			request[ bt.attr( 'name' ) ] = bt.val();
		} );
		SobiPro.jQuery( modal.find( ':button' ) ).each( function ( i, b )
		{
			var bt = SobiPro.jQuery( b );
			if ( bt.hasClass( 'active' ) ) {
				request[ bt.attr( 'name' ) ] = bt.val();
			}
		} );
		SobiPro.jQuery.ajax( {
			url: 'index.php',
			type: 'post',
			dataType: 'json',
			data: request
		} ).done( function ( response )
			{
				if ( response.redirect.execute ) {
					window.location.replace( response.redirect.url );
				}
				else {
					alert( response.message.text );
				}
			} );
	} );

	modal.find( '.ctrl-save-tpl' ).click( function ( e )
	{
		e.preventDefault();
		var name = window.prompt( SobiPro.Txt( 'ENTRY_REJECT_TEMPLATE_NAME_PROMPT' ), modal.find( 'select' ).find( ':selected' ).text() );
		if ( name && name.length ) {
			var request = {
				'option': 'com_sobipro',
				'task': 'config.saveRejectionTpl',
				'templateName': name,
				'sid': SobiProSection,
				'format': 'raw',
				'tmpl': 'component',
				'method': 'xhr',
				'reason': modal.find( 'textarea' ).val()
			};
			SobiPro.jQuery( modal.find( ':input' ) ).each( function ( i, b )
			{
				var bt = SobiPro.jQuery( b );
				request[ bt.attr( 'name' ) ] = bt.val();
			} );
			SobiPro.jQuery( modal.find( ':button' ) ).each( function ( i, b )
			{
				var bt = SobiPro.jQuery( b );
				if ( bt.hasClass( 'active' ) ) {
					request[ bt.attr( 'name' ) ] = bt.val();
				}
			} );

			SobiPro.jQuery.ajax( {
				url: 'index.php',
				type: 'post',
				dataType: 'json',
				data: request
			} ).done( function ( response )
				{
					alert( response.message.text );
					proxy.GetTemplates();
				} );
		}
	} );
};
