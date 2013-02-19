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
	SobiPro.jQuery( 'input:file' ).change( function ()
	{
		if ( !( SobiPro.jQuery( this ).hasClass( 'spFileUpload' ) ) && SobiPro.jQuery( this ).val() ) {
			SobiPro.jQuery( '#SP_method' ).val( 'html' );
		}
	} );
	function SpSerialAction( task )
	{
		var entries = [];
		var proxy = this;
		this.counter = 0;
		this.doneCounter = 0;
		this.progressBar = SobiPro.jQuery( '#SpProgress' ).find( '.bar' );
		this.progressMessage = SobiPro.jQuery( '#SpProgress .alert' );
		this.messages = { 'warning':[], 'error':[], 'info':[], 'success':[] };
		this.finish = function ( url )
		{
			var request = {'option':'com_sobipro', 'task':'txt.messages', 'format':'raw', 'method':'xhr', 'spsid':SobiPro.jQuery( '#SP_spsid' ).val() }
			SobiPro.jQuery.ajax( { 'url':'index.php', 'data':request, 'type':'post', 'dataType':'json',
				success:function ( response )
				{
					if ( response && response.data.messages.length ) {
						for ( var i = 0; i < response.data.messages.length; i++ ) {
							proxy.messages[ response.data.messages[ i ].type ].push( response.data.messages[ i ].text );
						}
					}
					var counter = 0;
					var output = []
					SobiPro.jQuery.each( proxy.messages, function ( type, reports )
					{
						var container = [];
						for ( var i = 0; i < reports.length; i++ ) {
							counter++;
							container.push( '<div><strong> ' + counter + ')&nbsp;</strong>' + reports[ i ] + '</div>' );
						}
						if ( container.length ) {
							output.push( '<div class="smallmessage alert-' + type + ' alert">' + container.join( "\n" ) + '</div>' );
						}
					} );
					if ( counter > 0 ) {
						var modal = '<div class="modal hide"><div class="modal-body">' + output.join( "\n" ) + '</div><div class="modal-footer"><a href="#" class="btn" data-dismiss="modal">OK</a></div></div>'
						SobiPro.jQuery( modal ).appendTo( SobiPro.jQuery( '#SobiPro' ) );
						var modalMessage = SobiPro.jQuery( modal ).modal();
						modalMessage.on( 'hidden', function ()
						{
							proxy.refresh( url );
						} );
					}
					else {
						proxy.refresh( url );
					}
				}
			} );
		}
		this.progress = function ( response )
		{
			this.doneCounter++;
			this.progressBar.css( 'width', 100 / ( this.counter - this.doneCounter + 1 ) + '%' );
			this.messageType( response.message.type );
			this.progressMessage.html( response.message.text );
			var url = response.redirect.url;
			if ( response.message.type != 'success' ) {
				this.messages[ response.message.type ].push( response.message.text );
			}
			if ( this.doneCounter == this.counter ) {
				this.finish( url );
			}
		}
		this.refresh = function ( url )
		{
			this.messageType( 'info' );
			this.progressMessage.html( SobiPro.Txt( 'PROGRESS_DONE_REDIRECTING' ) )
			window.location.replace( url );
		}
		this.messageType = function ( type )
		{
			this.progressMessage
				.removeClass( 'alert alert-info alert-success alert-error' )
				.addClass( 'alert alert-' + type );

		}
		SobiPro.jQuery( '[name="e_sid[]"]' ).each( function ( i, e )
		{
			var element = SobiPro.jQuery( e );
			if ( element.attr( 'checked' ) == 'checked' ) {
				entries.push( element );
			}
		} );
		if ( entries.length ) {
			this.counter = entries.length;
			this.progressMessage.html( SobiPro.Txt( 'PROGRESS_WORKING' ) );
			SobiPro.jQuery( '#SpProgress' ).removeClass( 'hide' );
			this.progressBar.css( 'width', '0%' );
			var request = {'option':'com_sobipro', 'task':task, 'format':'raw', 'method':'xhr', 'spsid':SobiPro.jQuery( '#SP_spsid' ).val()}
			for ( var i = 0; i < entries.length; i++ ) {
				request[ 'sid' ] = entries[ i ].val();
				SobiPro.jQuery.ajax( { 'url':'index.php', 'data':request, 'type':'post', 'dataType':'json', success:function ( response )
				{
					proxy.progress( response );
				} } );
			}
		}
	}

	var count = 0;
	var serialActions = [ 'entry.publish', 'entry.hide', 'entry.approve', 'entry.unapprove' ];
	SobiPro.jQuery( '#SPAdmToolbar a' ).click( function ( e )
	{
		if ( SobiPro.jQuery( this ).hasClass( 'legacy' ) ) {
			return false;
		}
		var task = SobiPro.jQuery( this ).attr( 'rel' );
		SobiPro.jQuery( '#SP_task' ).val( task );
		if ( task.length ) {
			e.preventDefault();
			e.stopPropagation();
			if ( SobiPro.jQuery.inArray( task, serialActions ) != -1 ) {
				SobiPro.jQuery( this ).parent().parent().parent().parent().removeClass( 'open' );
				return new SpSerialAction( task );
			}
			else if ( SobiPro.jQuery( '#SP_method' ).val() == 'xhr' ) {
				var handler = { 'takeOver':false };
				SobiPro.jQuery( '#SPAdminForm' ).trigger( 'BeforeAjaxSubmit', [ handler, task ] )
				if ( handler.takeOver == true ) {
					return true;
				}
				SPTriggerFrakingWYSIWYGEditors();
				var req = SobiPro.jQuery( '#SPAdminForm' ).serialize();
				SobiPro.jQuery( SobiPro.jQuery( '#SPAdminForm' ).find( ':button' ) ).each( function ( i, b )
				{
					bt = SobiPro.jQuery( b );
					if ( bt.attr( 'disabled' ) != 'disabled' && bt.hasClass( 'active' ) ) {
						req += '&' + bt.attr( 'name' ) + '=' + bt.val();
					}
				} );
				SobiPro.jQuery( '#SP_task' ).val( task );
				SobiPro.jQuery.ajax( {
					'url':'index.php',
					'data':req,
					'type':'post',
					'dataType':'json',
					success:function ( data )
					{
						if ( !( data.redirect.execute ) ) {
							var handler = { 'takeOver':false };
							SobiPro.jQuery( '#SPAdminForm' ).trigger( 'AfterAjaxSubmit', [ handler, data ] )
							if ( handler.takeOver == true ) {
								return true;
							}
							count++;
							c = '';
							if ( count > 1 ) {
								c = '&nbsp;(' + count + ')';
							}
							alert = '<div class="alert alert-' + data.message.type + '"><a class="close" data-dismiss="alert" href="#">×</a>' + data.message.text + c + '</div>';
							SobiPro.jQuery( '#spMessage' ).html( alert );
							try {
								SobiPro.jQuery.each( data.data.sets, function ( i, val )
								{
									SobiPro.jQuery( '[name^="' + i + '"]' ).val( val );
								} );
							}
							catch ( e ) {
							}
							if ( data.data.required ) {
								SobiPro.jQuery( '[name*="' + data.data.required + '"]' )
									.addClass( 'error' )
									.attr( 'required', 'required' )
									.focus()
									.focusout( function ()
									{
										if ( SobiPro.jQuery( this ).val() ) {
											SobiPro.jQuery( this )
												.removeClass( 'error' )
												.removeAttr( 'required' )
												.addClass( 'success' );
										}
									} )
								;
							}
						}
						else {
							window.location.replace( data.redirect.url );
						}
					}
				} );
			}
			else {
				SobiPro.jQuery( '#SPAdminForm' ).submit();
			}
		}
	} );

	SobiPro.jQuery( '.spOrdering' ).change( function ()
	{
		SobiPro.jQuery( '#SPAdminForm' ).submit();
	} );

	SobiPro.jQuery( '[name="spToggle"]' ).change( function ()
	{
		SobiPro.jQuery( '[name="' + SobiPro.jQuery( this ).attr( 'rel' ) + '[]"]' ).prop( 'checked', SobiPro.jQuery( this ).is( ':checked' ) );
	} );

	SobiPro.jQuery( '[name="spReorder"]' ).click( function ( e )
	{
		e.preventDefault();
		SobiPro.jQuery( '#SP_task' ).val( SobiPro.jQuery( this ).attr( 'rel' ) + '.reorder' );
		SobiPro.jQuery( '#SPAdminForm' ).submit();
	} );

	try {
		SobiPro.jQuery( '.counter-reset' ).each( function ( i, e )
		{
			"use strict";
			var el = SobiPro.jQuery( e );
			if ( el.html() == 0 ) {
				el.attr( 'disabled', 'disabled' );
			}
		} );
	}
	catch ( e ) {
	}
	SobiPro.jQuery( '.counter-reset' ).click( function ()
	{
		"use strict";
		var button = SobiPro.jQuery( this );
		if ( button.html() ) {
			SobiPro.jQuery.ajax( {
				'type':'post',
				'url':SobiProAdmUrl.replace( '%task%', button.attr( 'rel' ) + '.resetCounter' ),
				'data':{
					'sid':SobiPro.jQuery( '#SP_sid' ).val(),
					'format':'raw'
				},
				'dataType':'json',
				success:function ()
				{
					button.html( 0 );
					button.attr( 'disabled', 'disabled' );
				}
			} );
		}
		else {
			button.attr( 'disabled', 'disabled' );
		}
	} )
	function SPTriggerFrakingWYSIWYGEditors()
	{
		var events = [ 'unload', 'onbeforeunload', 'onunload' ];
		for ( var i = 0; i < events.length; i++ ) {
			try {
				window.dispatchEvent( events[ i ] );
			}
			catch ( e ) {
			}
			try {
				window.fireEvent( events[ i ] );
			}
			catch ( e ) {
			}
			try {
				SobiPro.jQuery( document ).triggerHandler( events[ i ] );
			}
			catch ( e ) {
			}
		}
		try {
			tinyMCE.triggerSave();
		}
		catch ( e ) {
		}
	}

	SobiPro.jQuery( '.spSubmit' ).keydown(
		function ( e )
		{
			"use strict";
			if ( e.keyCode == 13 ) {
				e.preventDefault();
				e.stopPropagation();
				SobiPro.jQuery( '#SPAdminForm' ).submit();
			}
		}
	);

	SobiPro.jQuery( '.buttons-radio :button' ).each( function ( i, e )
	{
		var e = SobiPro.jQuery( e );
		"use strict"
		if ( !( e.hasClass( 'selected' ) ) ) {
			e.removeClass( 'btn-success' )
				.removeClass( 'btn-danger' );
		}
		e.click( function ()
		{
			SobiPro.jQuery( e )
				.parent()
				.parent()
				.find( '.buttons-radio :button' )
				.removeClass( 'btn-danger' )
				.removeClass( 'btn-success' );
			switch ( parseInt( SobiPro.jQuery( this ).val() ) ) {
				case 0:
					e.addClass( 'btn-danger' );
					break;
				case 1:
					e.addClass( 'btn-success' );
					break;
			}
		} );
	} );
	try {
		SobiPro.jQuery( '#spcfg-general-show-pb' ).click( function ()
		{
			if ( SobiPro.jQuery( this ).find( '.active' ).val() == 1 ) {
				SobiPro.Alert( 'PBY_NO' );
			}
		} );
	}
	catch ( e ) {
	}
	//P_current-ip
	//
	try {
		SobiPro.jQuery( '#spcfg-debug-xml-ip' ).click( function ()
		{
			"use strict";
			if ( SobiPro.jQuery( this ).val() == '' ) {
				SobiPro.jQuery( this ).val( SobiPro.jQuery( '#SP_current-ip' ).val() );
			}
		} )
	}
	catch ( e ) {
	}
	if ( SobiPro.jQuery( '.spFileUpload' ).length ) {
		SobiPro.jQuery( '.spFileUpload' ).SPFileUploader();
	}
} );
