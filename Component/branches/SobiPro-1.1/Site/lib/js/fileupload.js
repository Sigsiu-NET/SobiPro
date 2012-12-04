/**
 * Created with JetBrains PhpStorm.
 * User: neo
 * Date: 3/12/2012
 * Time: 09:44
 * To change this template use File | Settings | File Templates.
 */
SobiPro.jQuery( document ).ready( function ()
{
	"use strict";
	SobiPro.jQuery( '.spFileUpload' ).find( 'input:file' ).change( function ()
		{
			if ( SobiPro.jQuery( this ).val() ) {
				SobiPro.jQuery( this ).parent().parent().find( '.btn' ).removeAttr( 'disabled' );
			}
		}
	);
	SobiPro.jQuery( '.spFileUpload .btn' ).click( function ()
	{
		var request = SobiPro.jQuery.parseJSON( SobiPro.jQuery( this ).attr( 'rel' ) );
		var container = SobiPro.jQuery( this ).parent().find( '.file' );
		var form = '<form action="index.php" method="post" enctype="multipart/form-data">';
		for ( var field in request ) {
			form += '<input type="hidden" value="' + request[ field ] + '" name="' + field + '"/>';
		}
		form += '</form>';
		form = SobiPro.jQuery( form );
		var file = SobiPro.jQuery( this ).parent().find( 'input:file' );
		file.appendTo( form );
		var c = file.clone( file );
		c.appendTo( container );
		var bar = SobiPro.jQuery( this ).parent().find( '.bar' );
		var responseContainer = SobiPro.jQuery( this ).parent().find( '.progress-container' );
		var progressMessage = SobiPro.jQuery( this ).parent().find( '.progress-message' );
		var responseMsg = SobiPro.jQuery( this ).parent().find( '.alert' );
		var idStore = SobiPro.jQuery( this ).parent().find( 'input:hidden' );
		var button = SobiPro.jQuery( this ).parent().find( '.btn' );
		form.ajaxForm( {
			'dataType':'json',
			beforeSend:function ()
			{
				responseContainer.removeClass( 'hide' );
				var percentVal = '0%';
				bar.width( percentVal );
			},
			uploadProgress:function ( event, position, total, percentComplete )
			{
				var percentVal = percentComplete + '%';
				bar.width( percentVal )
				progressMessage.html( percentVal );
			},
			complete:function ( xhr )
			{
				var response = SobiPro.jQuery.parseJSON( xhr.responseText );
//				responseContainer.addClass( 'hide' );
				responseMsg.removeClass( 'hide' );
				responseMsg.addClass( 'alert-' + response.type )
				responseMsg.find( 'div' ).html( response.text );
				idStore.val( response.id );
				button.attr( 'disabled', 'disabled' );
				file
			}
		} ).submit();
	} );
} );
