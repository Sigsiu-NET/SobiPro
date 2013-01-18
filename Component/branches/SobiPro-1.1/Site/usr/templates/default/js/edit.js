/**
 * @version: $Id$
 * @package: SobiPro Component for Joomla!

 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET

 * @copyright Copyright (C) 2006 - 2013 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license GNU/GPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3 as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/gpl.html and http://sobipro.sigsiu.net/licenses.

 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

 * $Date$
 * $Revision$
 * $Author$
 * $HeadURL$
 */

SobiPro.jQuery( document ).ready( function ()
{
	var template = '<div class="popover"><div class="arrow"></div><div class="popover-inner"><div class="pull-right close spclose">x</div><h3 class="popover-title"></h3><div class="popover-content"><p></p></div></div></div>';
	SobiPro.jQuery( 'a[rel=popover]' )
		.popover( { 'html':true, 'trigger':'click', 'template':template } )
		.click( function ( e )
		{
			e.preventDefault();
			var proxy = SobiPro.jQuery( this );
			SobiPro.DebOut( proxy )
			proxy.parent().find( '.close' ).click( function ()
			{
				proxy.popover( 'hide' );
			} )
		} );
	if ( SobiPro.jQuery( '.spFileUpload' ).length ) {
		SobiPro.jQuery( '.spFileUpload' ).SPFileUploader();
	}

	new SobiProEntryEdit();

	function SobiProEntryEdit()
	{
		"use strict";
		this.boxes = SobiPro.jQuery( '.payment-box' );
		var proxy = this;

		this.boxes.each( function ( i, element )
		{
			element = SobiPro.jQuery( element );
			element.toggleTarget = SobiPro.jQuery( '#' + element.attr( 'id' ).replace( '-payment', '-container' ) ).find( '*' );
			element.toggleTarget.attr( 'disabled', 'disabled' );
			element.change( function ()
			{
				if ( SobiPro.jQuery( this ).is( ':checked' ) ) {
					element.toggleTarget.removeAttr( 'disabled' );
				}
				else {
					element.toggleTarget.attr( 'disabled', 'disabled' );
				}
			} );
		} );
	}
} );
//
//
//try{ jQuery.noConflict(); } catch( e ) {}
//// it has to be MT :( because of the tiny
//window.addEvent( 'load', function() {
//	var els = SP_class( 'SPPaymentBox' );
//	for( var i = 0; i < els.length; i++ ) {
//		SP_ActivatePayment( SP_id( els[ i ].id ) );
//	}
//	$( 'spEntryForm' ).addEvent( 'submit', function( ev ) {
//		var els = SP_class( 'mce_editable' );
//		for( var i = 0; i < els.length; i++ ) {
//			if( tinyMCE.get( els[ i ].id ).getContent().length ) {
//				els[ i ].value = tinyMCE.get( els[ i ].id ).getContent();
//				els[ i ].disabled = false;
//			}
//		}
//	} );
//} );
//
//function SP_ActivatePayment( e )
//{
//	var cid = e.id.replace( 'Payment', 'Container' );
//	if( e.checked ) {
//		jQuery( "#" + cid + " input" ).each( function( i, el ){ this.disabled = false; } );
//		jQuery( "#" + cid + " select" ).each( function( i, el ){ this.disabled = false; } );
//		jQuery( "#" + cid + " textarea" ).each( function( i, el ){
//			if( el.className == 'mce_editable' ) {
//				tinyMCE.execCommand( 'mceToggleEditor', true, el.id );
//			}
//			else {
//				this.disabled = false;
//			}
//		} );
//	}
//	else {
//		jQuery( "#" + cid + " input" ).each( function( i, el ){ this.disabled = true; } );
//		jQuery( "#" + cid + " select" ).each( function( i, el ){ this.disabled = true; } );
//		jQuery( "#" + cid + " textarea" ).each( function( i, el ){
//			if( el.className == 'mce_editable' ) {
//				tinyMCE.execCommand( 'mceToggleEditor', false, el.id );
//			}
//			this.disabled = true;
//		} );
//	}
//	e.disabled = false;
//}
