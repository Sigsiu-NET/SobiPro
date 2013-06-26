/**
 * @version: $Id$
 * @package: SobiPro Library

 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET

 * @copyright Copyright (C) 2006 - 2013 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license GNU/LGPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License version 3
 *  as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/lgpl.html and http://sobipro.sigsiu.net/licenses.

 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

 * $Date$
 * $Revision$
 * $Author$
 * $HeadURL$
 */

SobiPro.jQuery.fn.SobiProFileUploader = function ( options )
{
	"use strict";
	var proxy = this;
	this.settings = {
		'hideProgressBar': true,
		'styles': {
			'.progress': {'clear': 'left', 'float': 'left', 'margin': '10px 10px 10px 0' },
			'.alert': {'clear': 'both' },
			'.file input': { 'margin-bottom': '10px'},
			'.progress-message': { 'margin-top': '10px'}
		}
	};
	this.settings = SobiPro.jQuery.extend( true, options, this.settings );
	SobiPro.jQuery.each( this.settings.styles, function ( element, styles )
	{
		proxy.find( element ).css( styles );
	} );
	var bar = proxy.find( '.bar' );
	var responseContainer = proxy.find( '.progress-container' );
	var progressMessage = proxy.find( '.progress-message' );
	var responseMsg = proxy.find( '.alert' );
	var idStore = proxy.find( '.idStore' );
	var button = proxy.find( '.upload' );

	this.complete = function ( xhr )
	{
		proxy.trigger( 'uploadComplete', [xhr] );
		var percentVal = '100%';
		bar.width( percentVal );
		progressMessage.html( percentVal );
		var response = SobiPro.jQuery.parseJSON( xhr.responseText );
		if ( proxy.settings.hideProgressBar ) {
			responseContainer.addClass( 'hide' );
		}
		if ( response.callback ) {
			var callback = window[ response.callback ];
			callback( response, proxy )
		}
		else {
			responseMsg.removeClass( 'hide' );
			responseMsg.addClass( 'alert-' + response.type );
			responseMsg.find( 'div' ).html( response.text );
			idStore.val( response.id );
			button.attr( 'disabled', 'disabled' );
		}
	};

	this.uploadProgress = function ( event, position, total, percentComplete )
	{
		proxy.trigger( 'uploadProgress', [ event, position, total, percentComplete ] );
		var percentVal = percentComplete + '%';
		bar.width( percentVal );
		progressMessage.html( percentVal );
	};

	this.beforeSend = function ()
	{
		proxy.trigger( 'beforeSend', [ this ] );
		responseContainer.removeClass( 'hide' );
		var percentVal = '0%';
		bar.width( percentVal );
		progressMessage.html( percentVal );
	};

	this.upload = function ()
	{
		var request = SobiPro.jQuery.parseJSON( proxy.find( '.upload' ).attr( 'rel' ) );
		proxy.trigger( 'createRequest', [ request ] );
		var container = proxy.find( '.file' );
		var file = proxy.find( 'input:file' );
		var id = file.attr( 'name' ) + '-form';
		var form = '<form action="' + 'index.php" method="post" enctype="multipart/form-data" id="' + id + '">';
		for ( var field in request ) {
			form += '<input type="hidden" value="' + request[ field ] + '" name="' + field + '"/>';
		}
		form += '</form>';
		form = SobiPro.jQuery( form );
		file.appendTo( form );
		var c = file.clone( file );
		c.appendTo( container );
		// frak you damn IE
		form.appendTo( SobiPro.jQuery( '#SobiPro' ) );
		SobiPro.jQuery( '#' + id ).ajaxForm( {
			'dataType': 'json',
			beforeSend: function ()
			{
				proxy.beforeSend();
			},
			uploadProgress: function ( event, position, total, percentComplete )
			{
				proxy.uploadProgress( event, position, total, percentComplete )
			},
			complete: function ( xhr )
			{
				proxy.complete( xhr );
			}
		} ).submit();
	};

	this.find( 'input:file' ).change( function ()
		{
			if ( SobiPro.jQuery( this ).val() ) {
				proxy.find( '.upload, .remove' ).removeAttr( 'disabled' );
				var fullPath = SobiPro.jQuery( this ).val();
				var startIndex = (fullPath.indexOf( '\\' ) >= 0 ? fullPath.lastIndexOf( '\\' ) : fullPath.lastIndexOf( '/' ));
				var filename = fullPath.substring( startIndex );
				if ( filename.indexOf( '\\' ) === 0 || filename.indexOf( '/' ) === 0 ) {
					filename = filename.substring( 1 );
				}
				proxy.find( '.selected' ).val( filename );
				setTimeout( function() { proxy.upload() }, 500 );
			}
		}
	);

	this.find( '.select' ).click( function ()
	{
		proxy.find( 'input:file' ).trigger( 'click' );
	} );

	this.find( '.remove' ).click( function ()
	{
		var file = proxy.find( 'input:file' );
		proxy.find( '.upload, .remove' ).attr( 'disabled', 'disabled' );
		proxy.find( '.selected' ).val( '' );
		proxy.find( 'idStore' ).val( '' );
		file.clone( file ).appendTo( file.parent() );
		file.detach()
	} );

	this.find( '.upload' ).click( function ()
	{
		proxy.upload();
	} );

	return this;
};

SobiPro.jQuery.fn.SPFileUploader = function ( options )
{
	return this.each( function ()
	{
		SobiPro.jQuery( this ).SobiProFileUploader( options );
	} );
};
