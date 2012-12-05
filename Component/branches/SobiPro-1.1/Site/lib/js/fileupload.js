/**
 * Created with JetBrains PhpStorm.
 * User: neo
 * Date: 3/12/2012
 * Time: 09:44
 * To change this template use File | Settings | File Templates.
 */
SobiPro.jQuery.fn.SPFileUploader = function ( options )
{
	"use strict";
	var proxy = this;
	this.settings = {
		'hideProgressBar':false,
		'styles':{
			'.progress':{'clear':'left', 'width':'500px', 'float':'left', 'margin':'10px' },
			'.alert':{'clear':'both', 'width':'500px' },
			'.file input':{ 'margin-bottom':'10px'},
			'.progress-message':{ 'margin-top':'10px'}
		}
	};
	this.settings = SobiPro.jQuery.extend( true, options, this.settings );
	SobiPro.jQuery.each( this.settings.styles, function ( element, styles )
	{
		proxy.find( element ).css( styles );
	} );

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
		proxy.find( 'input:hidden' ).val( '' );
		file.clone( file ).appendTo( file.parent() );
		file.detach()
	} );
	this.find( '.upload' ).click( function ()
	{
		var request = SobiPro.jQuery.parseJSON( SobiPro.jQuery( this ).attr( 'rel' ) );
		var container = proxy.find( '.file' );
		var form = '<form action="index.php" method="post" enctype="multipart/form-data">';
		for ( var field in request ) {
			form += '<input type="hidden" value="' + request[ field ] + '" name="' + field + '"/>';
		}
		form += '</form>';
		form = SobiPro.jQuery( form );
		var file = proxy.find( 'input:file' );
		file.appendTo( form );
		var c = file.clone( file );
		c.appendTo( container );
		var bar = proxy.find( '.bar' );
		var responseContainer = proxy.find( '.progress-container' );
		var progressMessage = proxy.find( '.progress-message' );
		var responseMsg = proxy.find( '.alert' );
		var idStore = proxy.find( 'input:hidden' );
		var button = proxy.find( '.upload' );
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
				if ( proxy.settings.hideProgressBar ) {
					responseContainer.addClass( 'hide' );
				}
				responseMsg.removeClass( 'hide' );
				responseMsg.addClass( 'alert-' + response.type )
				responseMsg.find( 'div' ).html( response.text );
				idStore.val( response.id );
				button.attr( 'disabled', 'disabled' );
			}
		} ).submit();
	} )
}
