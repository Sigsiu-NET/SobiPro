/**
 * @version: $Id$
 * @package: SobiPro Library

 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET

 * @copyright Copyright (C) 2006 - 2015 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
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
			var Id = SobiPro.jQuery( this ).parent().find( '.idStore' ).attr( 'name' );
			var Nid = Id.replace( 'field_', 'field.' );
			SobiPro.jQuery( this )
				.parent()
				.parent()
				.find( '.spEditImagePreview' )
				.html( '<img style="cursor:pointer;" id="' + Id + '_icon" class="spImageCrop" src="index.php?option=com_sobipro&task=' + Nid + '.icon&sid=' + SobiPro.jQuery( this ).data( 'section' ) + '&file=' + response.responseJSON.data.icon + '"/>' );
			SobiPro.jQuery( '#' + Id + '_icon' )
				.attr( 'data-width', response.responseJSON.data.width )
				.attr( 'data-height', response.responseJSON.data.height );
			SobiPro.jQuery( '.spImageCrop' ).click( function ()
			{
				var Id = SobiPro.jQuery( this ).attr( 'Id' ).replace( '_icon', '' );
				var Url = SobiPro.jQuery( this ).attr( 'src' ).replace( 'icon_', '' );
				var Pid = SobiPro.jQuery( this ).attr( 'Id' ).replace( '_icon', '_preview' );
				if ( SobiPro.jQuery( '#' + Id + '_modal' ).attr( 'data-image-url' ) != Url ) {
					SobiPro.jQuery( '#' + Id + '_modal' ).attr( 'data-image-url', Url );
					SobiPro.jQuery( '#' + Id + '_modal' )
						.find( '.modal-body' )
						.html( '<img src="' + Url + '" Id="' + Pid + '"/>' );
					var Proxy = SobiPro.jQuery( this );
					SobiPro.jQuery( '#' + Pid ).cropper( {
						aspectRatio: Proxy.data( 'width' ) / Proxy.data( 'height' ),
						data: {
							x: 0,
							y: 0
						},
						done: function ( data )
						{
							if ( data.length || true ) {
								SobiPro.jQuery( '#' + Id + '_modal' ).attr( 'data-coordinates', '::coordinates://' + JSON.stringify( {
									'x': data.x,
									'y': data.y,
									'height': data.height,
									'width': data.width
								} ) );
							}
						}
					} );
				}
				var Modal = SobiPro.jQuery( '#' + Id + '_modal' ).modal();
				Modal.find( 'a.save' ).click( function ( ev )
				{
					var Store = SobiPro.jQuery( '[name="' + Id + '"]' );
					var Current = Store.val();
					if ( Current && SobiPro.jQuery( '#' + Id + '_modal' ).data( 'coordinates' ).length ) {
						if ( Current.indexOf( 'coordinates://' ) != -1 ) {
							var currentArray = Current.split( '::coordinates://' );
							Store.val( currentArray[0] + SobiPro.jQuery( '#' + Id + '_modal' ).data( 'coordinates' ) );
						}
						else {
							Store.val( Current + SobiPro.jQuery( '#' + Id + '_modal' ).data( 'coordinates' ) );
						}
					}
				} )
			} );
		}
	} );
} );
