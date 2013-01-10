/**
 * @version: $Id: updates.js 551 2011-01-11 14:34:26Z Radek Suski $
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
 * $Date: 2011-01-11 15:34:26 +0100 (Tue, 11 Jan 2011) $
 * $Revision: 551 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/js/adm/updates.js $
 */


SobiPro.jQuery().ready( function ()
{
	"use strict";
	var SpMsgType = '';
	SobiPro.jQuery( '#SPAdminForm' ).on( 'BeforeAjaxSubmit', function ( e, handler, task )
	{
		if ( task == 'extensions.fetch' ) {
			handler.takeOver = true;
			SPSetCookie( '' );
			setTimeout( SPProgressMessage, 50 );
			SobiPro.jQuery( '#SpProgress' ).removeClass( 'hide' );
			SobiPro.jQuery( '#SpProgress .bar' ).css( 'width', '1%' );
			//noinspection JSUnresolvedVariable
			var def = SobiPro.jQuery( '#SP_method' ).next( 'input' );
			var url = SobiProAdmUrl.replace( '%task%', task ) + '&' + def.attr( 'name' ) + '=' + def.val();
			SobiPro.jQuery.ajax( {'type':'get', 'url':url, 'dataType':'json'} );
		}
	} );

	SobiPro.jQuery( '.SpAddRepo' ).click( function ()
	{
		new SpRepositoryInstall()
	} );

	function SpRepositoryInstall()
	{
		this.repository = SobiPro.jQuery( '[name="RepositoryURL"]' ).val();
		var proxy = this;
		this.error = function ( message )
		{
			var alertMsg = '<div class="alert alert-' + message.type + '"><a class="close" data-dismiss="alert" href="#">×</a>' + message.text + '</div>';
			SobiPro.jQuery( '#spMessage' )
				.removeClass( 'alert-info' )
				.html( alertMsg );

		}
		this.request = function ( request, callback )
		{
			SobiPro.jQuery.ajax( {
				'type':'post',
				'url':'index.php',
				'data':request,
				'dataType':'json',
				success:function ( data )
				{
					callback( data, request )
				}
			} );
		}

		this.addRepo = function ( data, request )
		{
			if ( data.message.type == 'error' ) {
				proxy.error( data.message );
			}
			else {
				SobiPro.jQuery( data.message.response ).appendTo( SobiPro.jQuery( '#SobiPro' ) );
				SobiPro.jQuery( '#SpRepoModal' ).find( '.modal' ).modal();
				SobiPro.jQuery( '#SpRepoModal' ).find( '.confirm' ).click( function ()
				{
					SobiPro.jQuery( '#SpRepoModal' ).remove();
					request[ 'task' ] = 'extensions.confirmRepo';
					proxy.request( request, proxy.repoCallback );
				} );
			}
		}

		this.repoCallback = function ( data, request )
		{
			if ( data.message.type == 'error' ) {
				proxy.error( data.message );
			}
			else {
				SobiPro.jQuery( data.message.response ).appendTo( SobiPro.jQuery( '#SobiPro' ) );
				SobiPro.jQuery( '#SpRepoModal' ).find( '.modal' ).modal();
				if ( data.repository != undefined ) {
					request[ 'repository' ] = data.repository;
				}
				if ( data.callback ) {
					SobiPro.jQuery( '#SpRepoModal' ).find( '.confirm' ).click( function ()
					{
						request[ 'task' ] = 'extensions.registerRepo';
						request[ 'callback' ] = data.callback;
						var form = SobiPro.jQuery( '#SpRepoModal' ).find( 'form' ).serializeArray();
						for ( var i = 0; i < form.length; i++ ) {
							request[ form[ i ][ 'name' ] ] = form[ i ][ 'value' ];
						}
						SobiPro.jQuery( '#SpRepoModal' ).remove();
						proxy.request( request, proxy.repoCallback )
					} );
				}
				else if ( data.redirect ) {
					SobiPro.Alert( data.message.response );
					window.location.reload();
				}
			}
		}

		if ( this.repository == '' ) {
			SobiPro.Alert( 'NO_REPO' );
		}
		else {
			var def = SobiPro.jQuery( '#SP_method' ).next( 'input' );
			var request = {
				'option':'com_sobipro',
				'task':'extensions.addRepo',
				'method':'xhr',
				'format':'raw',
				'repository':this.repository
			}
			request[ def.attr( 'name' ) ] = def.val();
			this.request( request, this.addRepo )
		}
	}

	function SPProgressMessage()
	{
		SobiPro.jQuery.ajax( {
			'type':'get',
			'url':SobiProAdmUrl.replace( '%task%', 'progress' ),
			'dataType':'json',
			success:function ( response )
			{
				if ( SpMsgType != response.type ) {
					SobiPro.jQuery( '#SpProgress .alert' )
						.removeClass( SpMsgType )
						.removeClass( 'alert-info' )
						.addClass( 'alert-' + response.type );
					SpMsgType = 'alert-' + response.type;
				}
				SobiPro.jQuery( '#SpProgress .alert' ).html( response.message );
				SobiPro.jQuery( '#SpProgress .bar' ).css( 'width', response.progress + '%' );
				if ( response.progress < 100 && response.type != 'error' ) {
					setTimeout( SPProgressMessage, response.interval );
				}
				else {
					if ( response.type != 'error' ) {
						window.location.reload();
					}
				}
			}
		} );
	}

	SobiPro.jQuery( '.SpExtInstall' ).click( function ()
	{
		new SpExtInstall( SobiPro.jQuery( this ).parent(), SobiPro.jQuery( this ).attr( 'rel' ) );
	} );

	SobiPro.jQuery( '.SpRemoveRepo' ).click( function ()
	{
		var repository = SobiPro.jQuery( this ).attr( 'rel' );
		var def = SobiPro.jQuery( '#SP_method' ).next( 'input' );
		var request = {
			'option':'com_sobipro',
			'task':'extensions.delRepo',
			'method':'xhr',
			'format':'raw',
			'repository':repository
		}
		request[ def.attr( 'name' ) ] = def.val();
		SobiPro.jQuery.ajax( {
				'type':'post',
				'url':'index.php',
				'data':request,
				'dataType':'json',
				success:function ( response )
				{
					window.location.replace( response.redirect.url );
				}
			}
		);
	} );

	function SpExtInstall( canvas, eid )
	{
		this.ident = eid.replace( /\./g, '-' );
		this.canvas = canvas;
		canvas.html( '<div id="' + this.ident + '"><div class="progress"><div class="bar" style="width: 1%;"></div></div></div>' );
		SPSetCookie( this.ident );
		var def = SobiPro.jQuery( '#SP_method' ).next( 'input' );
		var url = SobiProAdmUrl.replace( '%task%', 'extensions.download' ) + '&exid=' + eid + '&session=' + this.ident + '&' + def.attr( 'name' ) + '=' + def.val();
		this.progressBar = SobiPro.jQuery( '#' + this.ident ).find( '.bar' );
		var proxy = this;
		this.progress = function ()
		{
			SobiPro.jQuery.ajax( {
				'type':'get',
				'url':SobiProAdmUrl.replace( '%task%', 'progress' ) + '&session=' + proxy.ident,
				'dataType':'json',
				success:function ( response )
				{
					SobiPro.DebOut( response );
					if ( response.type != 'info' && response.type != 'success' ) {
						var labelType = response.type == 'error' ? 'important' : response.type;
						var label = '<span class="label label-' + labelType + '">' + response.typeText + '&nbsp;</span>';
						var modal = '<div class="modal hide" id="' + proxy.ident + 'Modal"><div class="modal-body"><p>' + label + response.message + '</p></div><div class="modal-footer"><a href="#" class="btn" data-dismiss="modal">OK</a></div></div>'
						SobiPro.jQuery( modal ).appendTo( proxy.canvas );
						var modalMessage = SobiPro.jQuery( '#' + proxy.ident + 'Modal' ).modal();
						SobiPro.jQuery( '#' + proxy.ident + 'Modal' ).find( '.btn' ).click( function ()
						{
							modalMessage.modal( 'hide' )
						} );

					}
					SobiPro.jQuery( proxy.progressBar ).css( 'width', response.progress + '%' );
					SobiPro.jQuery( proxy.progressBar ).html( response.progress + '%' );
					if ( response.progress < 100 && response.type != 'error' ) {
						setTimeout( function ()
						{
							proxy.progress();
						}, response.interval );
					}
				}
			} );
		}
		SobiPro.jQuery.ajax( {'type':'get', 'url':url, 'dataType':'json'} );
		setTimeout( function ()
		{
			proxy.progress();
		}, 500 );
	}

	function SPSetCookie( ident )
	{
		var expDate = new Date();
		expDate.setHours( expDate.getHours() + 1 );
		var cid = expDate.getTime() + Math.floor( Math.random() * 11 ) * 100;
		document.cookie = "SPro_ProgressMsg" + ident + "=" + cid + ";expires=" + expDate.toUTCString() + ";path=/";
	}
} );

function SPExtensionInstaller()
{
	window.location.reload();
}
