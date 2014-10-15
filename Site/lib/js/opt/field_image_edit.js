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
	SobiPro.jQuery( '.spImageUpload' ).bind( 'uploadComplete', function ( ev, response )
	{
		if ( response.responseJSON.data && response.responseJSON.data.icon && response.responseJSON.data.icon.length ) {
			var id = SobiPro.jQuery( this ).parent().find( '.idStore' ).attr( 'name' );
			var nid = id.replace( 'field_', 'field.' );
			SobiPro.jQuery( this )
				.parent()
				.find( '.spEditImagePreview' )
				.html( '<img style="cursor:pointer;" id="' + id + '_icon" class="spImageCrop" src="index.php?option=com_sobipro&task=' + nid + '.icon&sid=' + SobiProSection + '&file=' + response.responseJSON.data.icon + '"/>' );
			SobiPro.jQuery( '#' + id + '_icon' )
				.attr( 'data-width', response.responseJSON.data.width )
				.attr( 'data-height', response.responseJSON.data.height );
			SobiPro.jQuery( '.spImageCrop' ).click( function ()
			{
				var id = SobiPro.jQuery( this ).attr( 'id' ).replace( '_icon', '' );
				//var url = SobiPro.jQuery( this ).attr( 'src' ).replace( 'icon_', 'resized_' );
				var url = SobiPro.jQuery( this ).attr( 'src' ).replace( 'icon_', '' );
				var pId = SobiPro.jQuery( this ).attr( 'id' ).replace( '_icon', '_preview' );
				SobiPro.jQuery( '#' + id + '_modal' )
					.find( '.modal-body' )
					.html( '<img src="' + url + '" id="' + pId + '"/>' );
				var proxy = SobiPro.jQuery( this );
				SobiPro.jQuery( '#' + pId ).cropper( {
					aspectRatio: proxy.data( 'width' ) / proxy.data( 'height' ),
					data: {
						x: 0,
						y: 0
					},
					done: function ( data )
					{
						if ( data.x ) {
							SobiPro.jQuery( '#' + id + '_modal' ).attr( 'data-coordinates', '::coordinates://' + JSON.stringify( {
								'x': data.x,
								'y': data.y,
								'height': data.height,
								'width': data.width
							} ) );
						}
					}
				} );
				var modal = SobiPro.jQuery( '#' + id + '_modal' ).modal();
				modal.find( 'a.save' ).click( function ()
				{
					var store = proxy.parent().parent().parent().find( '.idStore' );
					var current = store.val();
					if ( current.indexOf( 'coordinates://' ) != -1 ) {
						var currentArray = current.split( 'coordinates://' );
						store.val( currentArray[0] + SobiPro.jQuery( '#' + id + '_modal' ).data( 'coordinates' ) );
					}
					else {
						store.val( current + SobiPro.jQuery( '#' + id + '_modal' ).data( 'coordinates' ) );
					}
				} )
			} );
		}
	} );
} );
