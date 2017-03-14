/**
 * @package: SobiPro Library
 *
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: https://www.Sigsiu.NET
 *
 * @copyright Copyright (C) 2006 - 2017 Sigsiu.NET GmbH (https://www.sigsiu.net). All rights reserved.
 * @license GNU/LGPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License version 3
 * as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/lgpl.html and https://www.sigsiu.net/licenses.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 * or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 */

SobiPro.jQuery( document ).ready( function ()
{
	try {
		SobiPro.jQuery( '.filter-edit' ).click( function ( e )
		{
			var FilterSaved = false;
			var requestUrl = SobiProAdmUrl.replace( '%task%', 'filter.edit' ) + '&tmpl=component';
			var fid = '';
			if ( SobiPro.jQuery( this ).attr( 'rel' ) ) {
				requestUrl += '&fid=' + SobiPro.jQuery( this ).attr( 'rel' );
				fid = SobiPro.jQuery( this ).attr( 'rel' );
			}
			SobiPro.jQuery( "#filter-edit-window" )
				//.css( 'width', '600px' )
				.find( '.modal-body' )
				.html( '<iframe src="' + requestUrl + '" id="filter-edit-window-frame"> </iframe>' );

			SobiPro.jQuery( '#filter-edit-window-frame' ).load( function ()
			{
				if ( SobiPro.jQuery( '#filter-edit-window-frame' ).contents().find( '#filter-regex' ).attr( 'readonly' ) == undefined ) {
					SobiPro.jQuery( '#filter-delete' )
						.removeAttr( 'disabled' );
					SobiPro.jQuery( '#filter-delete' ).click( function ()
					{
						var requestUrl = SobiProAdmUrl.replace( '%task%', 'filter.delete' ) + '&filter_id=' + fid;
						document.location = requestUrl;
					} );
				}
				else {
					SobiPro.jQuery( '#filter-delete' )
						.attr( 'disabled', 'disabled' );
				}
			} );

			if ( fid.length ) {
				if ( SobiPro.jQuery( "#filter-delete" ).attr( 'id' ) == undefined ) {
					SobiPro.jQuery( "#filter-edit-window" )
						.find( '.modal-footer' )
						.append( '<div class="pull-left"><button type="button" class="btn btn-danger" disabled="disabled" id="filter-delete" data-dismiss="modal">' + SobiPro.Txt( 'DELETE_FILTER' ) + '</a></div>' );
				}
			}
			else {
				SobiPro.jQuery( "#filter-delete" ).parent().remove();
			}
			SobiPro.jQuery( '#filter-edit-window' ).modal();
			SobiPro.jQuery( '#filter-edit-window' ).on( 'hidden', function ()
			{
				if ( FilterSaved ) {
					window.location.replace( String( window.location ).replace( '#', '' ) );
				}
			} );
			SobiPro.jQuery( "#filter-edit-window" )
				.find( '.save' )
				.click( function ( e )
				{
					FilterSaved = true;
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
} );
