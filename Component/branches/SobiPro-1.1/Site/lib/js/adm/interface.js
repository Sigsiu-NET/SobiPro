/**
 * Created with JetBrains PhpStorm.
 * User: neo
 * Date: 14/8/2012
 * Time: 17:34
 * To change this template use File | Settings | File Templates.
 */

SobiPro.jQuery( document ).ready( function ()
{
	SobiPro.jQuery( 'input:file' ).change( function ()
	{
		if ( !( SobiPro.jQuery( this ).hasClass( 'spFileUpload' ) ) && SobiPro.jQuery( this ).val() ) {
			SobiPro.jQuery( '#SP_method' ).val( 'html' );
		}
	} );
	var count = 0;
	SobiPro.jQuery( '#SPAdmToolbar a' ).click( function ( e )
	{
		var task = SobiPro.jQuery( this ).attr( 'rel' );
		SobiPro.jQuery( '#SP_task' ).val( task );
		if ( task.length ) {
			e.preventDefault();
			e.stopPropagation();
			if ( SobiPro.jQuery( '#SP_method' ).val() == 'xhr' ) {
				SPTriggerFrakingWYSIWYGEditors();
				req = SobiPro.jQuery( '#SPAdminForm' ).serialize();
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
		"use strict"
		if ( !( e.hasClass( 'selected' ) ) ) {
			e.removeClass( 'btn-success' )
				.removeClass( 'btn-danger' );
		}
		SobiPro.jQuery( e ).click( function ()
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
	try {
		SobiPro.jQuery( '.filter-edit' ).click( function ( e )
		{
			var requestUrl = SobiProAdmUrl.replace( '%task%', 'filter.edit' ) + '&tmpl=component';
			if ( SobiPro.jQuery( this ).attr( 'rel' ) ) {
				requestUrl += '&fid=' + SobiPro.jQuery( this ).attr( 'rel' );
			}
			SobiPro.jQuery( "#filter-edit-window" )
				.css( 'width', '720px' )
				.find( '.modal-body' )
				.html( '<iframe src="' + requestUrl + '" id="filter-edit-window-frame" style="width: 690px; height: 250px; border: none;"> </iframe>' );
			SobiPro.jQuery( '#filter-edit-window' ).modal();
//			SobiPro.jQuery( "#filter-edit-window" )
//				.find( '.save' )
//				.click( function ( e )
//				{
//					"use strict";
//					window.location.replace( window.location );
//				} );
			SobiPro.jQuery( "#filter-edit-window" )
				.find( '.save' )
				.click( function ( e )
				{
					SobiPro.jQuery.ajax( {
						url:'index.php',
						data:SobiPro.jQuery( '#filter-edit-window-frame' ).contents().find( 'body #SPAdminForm' ).serialize(),
						type:'post',
						dataType:'json',
						success:function ( data )
						{
							iframe = SobiPro.jQuery( '#filter-edit-window-frame' ).contents().find( 'body #SPAdminForm' );
							alert = '<div class="alert alert-' + data.message.type + '"><a class="close" data-dismiss="alert" href="#">×</a>' + data.message.text + '</div>';
							iframe.find( '#spMessage' ).html( alert );
							if ( data.data.required ) {
								stop = true;
								iframe.find( '[name^="' + data.data.required + '"]' )
									.addClass( 'error' )
									.focus()
									.focusout( function ()
									{
										if ( SobiPro.jQuery( this ).val() ) {
											SobiPro.jQuery( this )
												.removeClass( 'error' )
												.addClass( 'success' );
										}
									} );
							}
						}
					} );
					e.stopPropagation();
				} );
		} );
	}
	catch ( e ) {
	}
	SobiPro.jQuery( 'a[rel=tooltip]' )
		.tooltip( { 'html':true } )
		.click( function ( e )
		{
			e.preventDefault()
		} );
	var template = '<div class="popover"><div class="arrow"></div><div class="popover-inner"><div class="pull-right close spclose">x</div><h3 class="popover-title"></h3><div class="popover-content"><p></p></div></div></div>';
	SobiPro.jQuery( 'a[rel=popover]' )
		.popover( { 'html':true, 'trigger':'click', 'placement':'top', 'template':template } )
		.click( function ( e )
		{
			e.preventDefault();
			var proxy = SobiPro.jQuery( this );
			SobiPro.jQuery( this ).parent().find( '.popover' ).find( '.close' ).click( function ()
			{
				proxy.popover( 'hide' );
			} )
		} );
	if ( SobiPro.jQuery( '.spFileUpload' ).length ) {
		SobiPro.jQuery( '.spFileUpload' ).SPFileUploader();
	}
} );
