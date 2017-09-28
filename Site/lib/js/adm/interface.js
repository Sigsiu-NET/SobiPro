/**
 * @package: SobiPro Library
 *
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: https://www.Sigsiu.NET
 *
 * @copyright Copyright (C) 2006 - 2017 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license GNU/LGPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License version 3
 * as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/lgpl.html and https://www.sigsiu.net/licenses.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 * or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 */

SobiPro.jQuery( document ).ready( function () {
	SobiPro.jQuery( 'input:file' ).change( function () {
		if ( ! ( SobiPro.jQuery( this ).hasClass( 'spFileUploadHidden' ) ) && SobiPro.jQuery( this ).val() ) {
			SobiPro.jQuery( '#SP_method' ).val( 'html' );
		}
	} );

	function SpSerialAction( task )
	{
		let entries = [];
		let proxy = this;
		this.counter = 0;
		this.doneCounter = 0;
		this.progressBar = SobiPro.jQuery( '#SpProgress' ).find( '.bar' );
		this.progressMessage = SobiPro.jQuery( '#SpProgress .alert' );
		this.messages = { 'warning': [], 'error': [], 'info': [], 'success': [] };
		this.finish = function ( url ) {
			const request = {
				'option': 'com_sobipro',
				'task': 'txt.messages',
				'format': 'raw',
				'method': 'xhr',
				'spsid': SobiPro.jQuery( '#SP_spsid' ).val()
			};
			SobiPro.jQuery.ajax( {
				'url': 'index.php', 'data': request, 'type': 'post', 'dataType': 'json',
				success: function ( response ) {
					if ( response && response.data.messages.length ) {
						for ( let i = 0; i < response.data.messages.length; i ++ ) {
							proxy.messages[ response.data.messages[ i ].type ].push( response.data.messages[ i ].text );
						}
					}
					let counter = 0;
					let output = [];
					SobiPro.jQuery.each( proxy.messages, function ( type, reports ) {
						let container = [];
						for ( let i = 0; i < reports.length; i ++ ) {
							counter ++;
							container.push( '<div><strong> ' + counter + ')&nbsp;</strong>' + reports[ i ] + '</div>' );
						}
						if ( container.length ) {
							output.push( '<div class="smallmessage alert-' + type + ' alert">' + container.join( "\n" ) + '</div>' );
						}
					} );
					if ( counter > 0 ) {
						let modal = '<div class="modal hide" id="SpModalMsg"><div class="modal-body">' + output.join( "\n" ) + '</div><div class="modal-footer"><a href="#" class="btn" data-dismiss="modal">OK</a></div></div>';
						SobiPro.jQuery( modal ).appendTo( SobiPro.jQuery( '#SobiPro' ) );
						let modalMessage = SobiPro.jQuery( '#SpModalMsg' ).modal();
						modalMessage.on( 'hidden', function () {
							proxy.refresh( url );
						} );
					}
					else {
						proxy.refresh( url );
					}
				}
			} );
		};
		this.progress = function ( response ) {
			this.doneCounter ++;
			this.progressBar.css( 'width', 100 / ( this.counter - this.doneCounter + 1 ) + '%' );
			this.messageType( response.message.type );
			this.progressMessage.html( response.message.text );
			let url = response.redirect.url;
			if ( response.message.type != 'success' ) {
				this.messages[ response.message.type ].push( response.message.text );
			}
			if ( this.doneCounter == this.counter ) {
				this.finish( url );
			}
		};

		this.refresh = function ( url ) {
			this.messageType( 'info' );
			this.progressMessage.html( SobiPro.Txt( 'PROGRESS_DONE_REDIRECTING' ) );
			window.location.replace( url );
		};

		this.messageType = function ( type ) {
			this.progressMessage
				.removeClass( 'alert alert-info alert-success alert-error' )
				.addClass( 'alert alert-' + type );

		};
		SobiPro.jQuery( '[name="e_sid[]"]' ).each( function ( i, e ) {
			let element = SobiPro.jQuery( e );
			if ( element.prop( 'checked' ) ) {
				entries.push( element );
			}
		} );
		const request = {
			'option': 'com_sobipro',
			'task': task,
			'format': 'raw',
			'method': 'xhr',
			'spsid': SobiPro.jQuery( '#SP_spsid' ).val()
		};
		if ( entries.length ) {
			this.counter = entries.length;
			this.progressMessage.html( SobiPro.Txt( 'PROGRESS_WORKING' ) );
			SobiPro.jQuery( '#SpProgress' ).removeClass( 'hide' );
			this.progressBar.css( 'width', '0%' );
			for ( let i = 0; i < entries.length; i ++ ) {
				request[ 'sid' ] = entries[ i ].val();
				SobiPro.jQuery.ajax( {
					'url': 'index.php',
					'data': request,
					'type': 'post',
					'dataType': 'json',
					success: function ( response ) {
						proxy.progress( response );
					}
				} );
			}
		}
		else {
			// delete all entries in a section
			request[ SobiPro.jQuery( '#SP_task' ).next().attr( 'name' ) ] = 1;
			request[ 'sid' ] = SobiPro.jQuery( '#SP_pid' ).val();
			SobiPro.jQuery.ajax( {
				'url': 'index.php',
				'data': request,
				'type': 'post',
				'dataType': 'json',
				success: function ( response ) {
					SobiPro.jQuery( '#SpProgress' ).removeClass( 'hide' );
					proxy.counter = response.data.counter;
					proxy.progress( response );
					SobiPro.jQuery.each( response.data.entries, function ( i, e ) {
						request[ 'eid' ] = e;
						SobiPro.jQuery.ajax( {
							'url': 'index.php',
							'data': request,
							'type': 'post',
							'dataType': 'json',
							success: function ( response ) {
								proxy.progress( response );
							}
						} );
					} );
				}
			} );
		}
	}

	let count = 0;
	let serialActions = [ 'entry.publish', 'entry.hide', 'entry.approve', 'entry.unapprove', 'entry.deleteAll' ];
	SobiPro.jQuery( '#SPAdmToolbar a' ).click( function ( e ) {
		if ( SobiPro.jQuery( this ).attr( 'title' ) ) {
			if ( ! ( confirm( SobiPro.jQuery( this ).attr( 'title' ) ) ) ) {
				e.preventDefault();
				e.stopPropagation();
				return false;
			}
		}
		if ( SobiPro.jQuery( this ).hasClass( 'legacy' ) ) {
			return false;
		}
		var task = SobiPro.jQuery( this ).attr( 'rel' );
		SobiPro.jQuery( '#SP_task' ).val( task );
		if ( task.length ) {
			e.preventDefault();
			e.stopPropagation();
			if ( SobiPro.jQuery.inArray( task, serialActions ) != - 1 ) {
				SobiPro.jQuery( this ).parent().parent().parent().parent().removeClass( 'open' );
				return new SpSerialAction( task );
			}
			else if ( SobiPro.jQuery( '#SP_method' ).val() == 'xhr' ) {
				SobiPro.jQuery( '#SP_task' ).val( task );
				if ( (( task == 'entry.save' || task == 'entry.apply' ) && SobiPro.jQuery( '#SP_history-note' ).length && SobiPro.jQuery( '#SP_history-note' ).val() != 0 ) || task == 'entry.saveWithRevision' ) {
					var note = prompt( SobiPro.Txt( 'HISTORY_NOTE' ), '' );
					if ( ( typeof note ) == 'string' ) {
						SobiPro.jQuery( '#SP_history-note' ).val( note );
					}
					else {
						return;
					}
				}
				var handler = { 'takeOver': false };
				SobiPro.jQuery( '#SPAdminForm' ).trigger( 'BeforeAjaxSubmit', [ handler, task ] );
				if ( handler.takeOver == true ) {
					return true;
				}
				SPTriggerFrakingWYSIWYGEditors();
				var req = SobiPro.jQuery( '#SPAdminForm' ).serialize();
				SobiPro.jQuery( SobiPro.jQuery( '#SPAdminForm' ).find( ':button' ) ).each( function ( i, b ) {
					var bt = SobiPro.jQuery( b );
					if ( bt.attr( 'disabled' ) != 'disabled' && bt.hasClass( 'active' ) ) {
						req += '&' + bt.attr( 'name' ) + '=' + bt.val();
					}
				} );
				SobiPro.jQuery( '#SobiPro' ).css( 'opacity', 0.3 );
				SobiPro.jQuery( '#SobiPro' ).before( SobiPro.jQuery( '#SpSpinner' ) );
				SobiPro.jQuery( '#SpSpinner' ).removeClass( 'hide' );
				SobiPro.jQuery.ajax( {
					'url': 'index.php',
					'data': req,
					'type': 'post',
					'dataType': 'json',
					success: function ( data ) {
						if ( ! ( data.redirect.execute ) ) {
							var handler = { 'takeOver': false };
							SobiPro.jQuery( '#SPAdminForm' ).trigger( 'AfterAjaxSubmit', [ handler, data ] );
							if ( handler.takeOver == true ) {
								return true;
							}
							count ++;
							c = '';
							if ( count > 1 ) {
								c = '&nbsp;(' + count + ')';
							}
							SobiPro.jQuery( '#SobiPro' ).css( 'opacity', 1 );
							SobiPro.jQuery( '#SpSpinner' ).addClass( 'hide' );
							var Message = '<div class="alert alert-' + data.message.type + '"><a class="close" data-dismiss="alert" href="#">×</a>' + data.message.text + c + '</div>';
							SobiPro.jQuery( '#spMessage' ).html( Message );
							try {
								SobiPro.jQuery.each( data.data.sets, function ( i, val ) {
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
									.focusout( function () {
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

	SobiPro.jQuery( '.spOrdering' ).change( function () {
		SobiPro.jQuery( '#SPAdminForm' ).submit();
	} );

	SobiPro.jQuery( '[name="spToggle"]' ).change( function () {
		SobiPro.jQuery( this ).parent().parent().parent().parent().parent().find( '[name="' + SobiPro.jQuery( this ).attr( 'rel' ) + '[]"]' ).prop( 'checked', SobiPro.jQuery( this ).is( ':checked' ) );
	} );

	SobiPro.jQuery( '[name="spReorder"]' ).click( function ( e ) {
		e.preventDefault();
		SobiPro.jQuery( '#SP_task' ).val( SobiPro.jQuery( this ).attr( 'rel' ) + '.reorder' );
		SobiPro.jQuery( '#SPAdminForm' ).submit();
	} );

	try {
		SobiPro.jQuery( '.counter-reset' ).each( function ( i, e ) {
			"use strict";
			var el = SobiPro.jQuery( e );
			if ( el.html() == 0 ) {
				el.attr( 'disabled', 'disabled' );
			}
		} );
	}
	catch ( e ) {
	}
	SobiPro.jQuery( '.counter-reset' ).click( function () {
		var button = SobiPro.jQuery( this );
		var sid = SobiPro.jQuery( '#SP_sid' ).val() ? SobiPro.jQuery( '#SP_sid' ).val() : SobiPro.jQuery( '[name^="category.id"]' ).val();
		if ( button.html() ) {
			SobiPro.jQuery.ajax( {
				'type': 'post',
				'url': SobiProAdmUrl.replace( '%task%', button.attr( 'rel' ) + '.resetCounter' ),
				'data': {
					'sid': sid,
					'format': 'raw'
				},
				'dataType': 'json',
				success: function () {
					button.html( 0 );
					button.attr( 'disabled', 'disabled' );
				}
			} );
		}
		else {
			button.attr( 'disabled', 'disabled' );
		}
	} );

	function SPTriggerFrakingWYSIWYGEditors()
	{

		try {
			var Editors = Object.keys( tinyMCE.editors );
			SobiPro.jQuery.each( Editors, function ( i, eid ) {
				if ( eid != 0 ) {
					// facepalm - mceAddControl is simply not working
					tinyMCE.execCommand( 'mceToggleEditor', false, eid );
					tinyMCE.execCommand( 'mceToggleEditor', false, eid );
				}
			} );
		}
		catch ( e ) {
		}
		var events = [ 'unload', 'onbeforeunload', 'onunload' ];
		for ( var i = 0; i < events.length; i ++ ) {
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
			try {
			}
			catch ( e ) {
			}
		}
		try {
			tinyMCE.triggerSave();
		}
		catch ( e ) {
		}
		SobiPro.jQuery.each( Joomla.editors.instances, function () {
			try {
				this.save();
			}
			catch ( e ) {
			}
		} );
	}

	SobiPro.jQuery( '.spSubmit' ).keydown(
		function ( e ) {
			"use strict";
			if ( e.keyCode == 13 ) {
				e.preventDefault();
				e.stopPropagation();
				SobiPro.jQuery( '#SPAdminForm' ).submit();
			}
		}
	);

	SobiPro.jQuery( '.spDisableEnter' ).keydown(
		function ( e ) {
			"use strict";
			if ( e.keyCode == 13 ) {
				e.preventDefault();
				e.stopPropagation();
			}
		}
	);

	SobiPro.jQuery( '.buttons-radio :button' ).each( function ( i, e ) {
		var e = SobiPro.jQuery( e );
		if ( ! ( e.hasClass( 'selected' ) ) ) {
			e.removeClass( 'btn-success' )
				.removeClass( 'btn-danger' );
		}
		e.click( function () {
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
		SobiPro.jQuery( '#spcfg-general-show-pb' ).click( function () {
			if ( SobiPro.jQuery( this ).find( '.active' ).val() == 1 ) {
				SobiPro.Alert( 'PBY_NO' );
			}
		} );
	}
	catch ( e ) {
	}
	//P_current-ip
	try {
		SobiPro.jQuery( '#spcfg-debug-xml-ip' ).click( function () {
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

	function spKeepAlive()
	{
		jQuery.ajax( { url: 'index.php' } );
		setTimeout( spKeepAlive, 300000 );
	}

	spKeepAlive();

	SobiPro.jQuery( '.ctrl-default-ordering' ).click( function ( e ) {
		e.preventDefault();
		if ( confirm( SobiPro.Txt( 'STORE_DEFAULT_ORDERING' ) ) ) {
			SobiPro.jQuery.ajax( {
				'type': 'post',
				'url': 'index.php',
				'data': {
					'sid': SobiProSection,
					'format': 'raw',
					'option': 'com_sobipro',
					'tmpl': 'component',
					'task': 'config.saveOrdering',
					'target': SobiPro.jQuery( this ).data( 'target' ),
					'method': 'xhr'
				}
			} ).done( function ( data ) {
				var Message = '<div class="alert alert-' + data.message.type + '"><a class="close" data-dismiss="alert" href="#">×</a>' + data.message.text + '</div>';
				SobiPro.jQuery( '#spMessage' ).html( Message );
			} );
		}
	} );

	SobiPro.jQuery( 'a[href="#fmn-categories-fields"]' ).click( function () {
		SobiPro.jQuery( '#SobiPro .btn-group' )[ 0 ].hide();
		SobiPro.jQuery( '#SobiPro .btn-group' )[ 1 ].show();
	} );
	SobiPro.jQuery( 'a[href="#fmn-entry-fields"]' ).click( function () {
		SobiPro.jQuery( '#SobiPro .btn-group' )[ 1 ].hide();
		SobiPro.jQuery( '#SobiPro .btn-group' )[ 0 ].show();
	} );

	SobiPro.jQuery( window ).keydown( function ( e ) {
		if ( e.which == 224 || e.which == 17 || e.which == 91 || e.which == 93 ) {
			SobiPro.cmdKey = true;
		}
		else {
			if ( (e.which == 115 || e.which == 19 || e.keyCode == 83) && (e.ctrlKey || e.cmdKey || SobiPro.cmdKey ) ) {
				e.preventDefault();
				try {
					e.preventDefault();
					if ( SobiPro.jQuery( '.spIconBar' ).find( '[rel*="\.apply"]' ).length ) {
						SobiPro.jQuery( '.spIconBar' ).find( '[rel*="\.apply"]' ).click();
					}
					else if ( SobiPro.jQuery( '.spIconBar' ).find( '[rel*="\.saveConfig"]' ).length ) {
						SobiPro.jQuery( '.spIconBar' ).find( '[rel*="\.saveConfig"]' ).click();
					}
					else {
						SobiPro.jQuery( '.spIconBar' ).find( '[rel$="\.save"]' ).click();
					}
				}
				catch ( x ) {
				}
			}
			SobiPro.cmdKey = false;
		}
	} );

	SobiPro.jQuery( 'ul.nav-tabs > li > a' ).on( 'shown.bs.tab', function ( e ) {
		try {
			localStorage.setItem( 'SobiProOpenTab', SobiPro.jQuery( e.target ).attr( 'href' ) );
		}
		catch ( x ) {
		}
	} );
	var lastTab = localStorage.getItem( 'SobiProOpenTab' );
	try {
		if ( lastTab ) {
			SobiPro.jQuery( '[href="' + lastTab + '"]' ).tab( 'show' );
		}
	}
	catch ( x ) {
	}

	try {
		if ( SobiPro.jQuery( '.active[name="category.allFields"]' ).hasClass( 'btn-success' ) ) {
			SobiPro.jQuery( '.entryFields' ).fadeTo( 'slow', 0.2 );
		}
		SobiPro.jQuery( '[value="0"][name="category.allFields"]' ).click( function () {
			SobiPro.jQuery( '.entryFields' ).fadeTo( 'slow', 1 );
		} );
		SobiPro.jQuery( '[value="1"][name="category.allFields"]' ).click( function () {
			SobiPro.jQuery( '.entryFields' ).fadeTo( 'slow', 0.2 );
		} );
	}
	catch ( x ) {
	}

} );
