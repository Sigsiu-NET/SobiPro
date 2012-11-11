/**
 * Created with JetBrains PhpStorm.
 * User: neo
 * Date: 14/8/2012
 * Time: 17:34
 * To change this template use File | Settings | File Templates.
 */

SobiPro.jQuery( document ).ready( function ()
{
	var count = 0;
	SobiPro.jQuery( '#SPAdmToolbar a' ).click( function ( e )
	{
		var task = SobiPro.jQuery( this ).attr( 'rel' );
		SobiPro.jQuery( '#SP_task' ).val( task );
		if ( task.length ) {
			e.preventDefault();
			if ( SobiPro.jQuery( '#SP_method' ).val() == 'xhr' ) {
				SPTriggerFrakingWYSIWYGEditors();
				req = SobiPro.jQuery( '#SPAdminForm' ).serialize();
				buttons = {};
				SobiPro.jQuery( SobiPro.jQuery( '#SPAdminForm' ).find( ':button' ) ).each( function ( i, b )
				{
					bt = SobiPro.jQuery( b );
					if ( bt.hasClass( 'active' ) ) {
						req += '&' + bt.attr( 'name' ) + '=' + bt.val();
					}
				} );
				SobiPro.jQuery( '#SP_task' ).val( task );
				SobiPro.jQuery.ajax( {
					url:'index.php',
					data:req,
					type:'post',
					dataType:'json',
					success:function ( data )
					{
						if ( !( data.redirect.execute ) ) {
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
								SobiPro.jQuery( '[name^="' + data.data.required + '"]' )
									.addClass( 'error' )
									.focus()
									.focusout( function ()
									{
										if ( SobiPro.jQuery( this ).val() ) {
											SobiPro.jQuery( this )
												.removeClass( 'error' )
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
		if ( SobiPro.jQuery( '.spOrdering' ).val().indexOf( 'order' ) == -1 ) {
			SobiPro.jQuery( '[name="spReorder"]' ).attr( 'disabled', 'disabled' );
		}
	}
	catch ( e ) {
	}

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
					'sid':SobiPro.jQuery( '[name^="' + button.attr( 'rel' ) + '.id"]' ).val(),
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
		"use strict";
		var events = [ 'unload', 'onbeforeunload', 'onunload' ];
		for ( var i = 0; i < events.length; i++ ) {
			try { window.dispatchEvent( events[ i ] ); } catch ( e ) {}
			try { window.fireEvent( events[ i ] ); } catch ( e ) {}
			try { SobiPro.jQuery( document ).triggerHandler( events[ i ] ); } catch ( e ) {}
		}
		tinyMCE.triggerSave();

	}
} );
