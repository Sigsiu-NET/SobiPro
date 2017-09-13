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

 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 *
 * This is the default JavaScript for the edit screen. It requires a default or default based frontend template
 */

SobiPro.jQuery( document ).ready( function ()
{
    var template = '<div class="popover"><div class="arrow"></div><div class="popover-inner"><div class="pull-right close spclose">x</div><h3 class="popover-title"></h3><div class="popover-content"><p></p></div></div></div>';
    SobiPro.jQuery( 'a[rel=popover]' )
        .popover( { 'html': true, 'trigger': 'click', 'template': template } )
        .click( function ( e )
        {
            e.preventDefault();
            var proxy = SobiPro.jQuery( this );
            proxy.parent().find( '.close' ).click( function ()
            {
                proxy.popover( 'hide' );
            } );
        } );

    setTimeout( function ()
    {
        new SobiProEntryEdit();
    }, 1000 );

    function SobiProEntryEdit()
    {
        "use strict";
        this.boxes = SobiPro.jQuery( '.payment-box' );
        var proxy = this;

        this.boxes.each( function ( i, element )
        {
            element = SobiPro.jQuery( element );
            element.targetContainer = SobiPro.jQuery( '#' + element.attr( 'id' ).replace( '-payment', '-input-container' ) );
            element.toggleTarget = element.targetContainer.find( '*' ).not('option');
            element.targetIframes = element.targetContainer.find( 'iframe' ).parent();
            element.disableTargets = function ()
            {
                this.toggleTarget.attr( 'disabled', 'disabled' );
                this.targetContainer.children().css( 'opacity', '0.3' );
                this.targetIframes.css( 'display', 'none' );
            };
            element.disableTargets();
            element.change( function ()
            {
                if ( SobiPro.jQuery( this ).is( ':checked' ) ) {
                    element.toggleTarget.removeAttr( 'disabled' );
                    element.targetContainer.children().css( 'opacity', '1' );
                    element.targetIframes.css( 'display', '' );
                }
                else {
                    element.disableTargets();
                }
            } );
        } );

        this.sendRequest = function ()
        {
            var request = SobiPro.jQuery( '#spEntryForm' ).serialize();
            SobiPro.jQuery( SobiPro.jQuery( '#spEntryForm' ).find( ':button' ) ).each( function ( i, b )
            {
                var bt = SobiPro.jQuery( b );
                if ( bt.hasClass( 'active' ) ) {
                    request += '&' + bt.attr( 'name' ) + '=' + bt.val();
                }
            } );
            SobiPro.jQuery.ajax( {
                'url': 'index.php',
                'data': request,
                'type': 'post',
                'dataType': 'json',
                success: function ( response )
                {
                    if ( response.message.type == 'error' ) {
                        proxy.errorHandler( response );
                    }
                    else {
                        if ( response.redirect.execute == true ) {
                            window.location.replace( response.redirect.url );
                        }
                        else if ( response.message.type == 'info' ) {
                            SobiPro.jQuery( response.message.text ).appendTo( SobiPro.jQuery( '#SobiPro' ) );
                            var modal = SobiPro.jQuery( '#SpPaymentModal' ).find( '.modal' ).modal();
                            modal.on( 'hidden', function ()
                            {
                                SobiPro.jQuery( '#SpPaymentModal' ).remove();
                            } );
                            modal.on( 'hidden.bs.modal', function ()
                            {
                                SobiPro.jQuery( '#SpPaymentModal' ).remove();
                            } );
                        }
                    }
                }
            } );
        };

        this.dismissAlert = function ( popover, attach, container )
        {
            popover.popover( 'hide' );
            attach.addClass( 'hide' );
            popover.remove();
            container.removeClass( 'error' );
        };

        this.errorHandler = function ( response )
        {
            var input = SobiPro.jQuery( '#' + response.data.error );
            var attach = SobiPro.jQuery( '#' + response.data.error + '-message' );
            var container = SobiPro.jQuery( '#' + response.data.error + '-container' );
            container.addClass( 'error' );
            var placement = 'bottom';
            if ( attach.length ) {
                var popover = SobiPro.jQuery( '<a class="sobipro-input-note" data-placement="' + placement + '" rel="popover" data-content="' + response.message.text + '" data-original-title="' + SobiPro.Txt( 'ATTENTION' ) + '">&nbsp;</a>' );
                attach.append( popover );
                attach.removeClass( 'hide' );
                popover.popover( {'template': template} );
                popover.popover( 'show' );
                attach.find( '.close' ).click( function ()
                {
                    proxy.dismissAlert( popover, attach, container );
                } );
                attach.ScrollTo();
                input.focus( function ()
                {
                    proxy.dismissAlert( popover, attach, container );
                } );
                if ( placement == 'top' ) {
                    container.find( ':input' ).focus( function ()
                    {
                        proxy.dismissAlert( popover, attach, container );
                    } );
                }
            }
            else {
                alert( response.message.text );
            }
        };

        SobiPro.jQuery( '.sobipro-submit' ).click( function ( e )
        {
            SPTriggerFrakingWYSIWYGEditors();
            proxy.sendRequest();
        } );
        SobiPro.jQuery( '.sobipro-cancel' ).click( function ( e )
        {
            SobiPro.jQuery( '#SP_task' ).val( 'entry.cancel' );
            proxy.sendRequest();
        } );
    }
} );
function SPTriggerFrakingWYSIWYGEditors()
{
	try {
		var Editors = Object.keys( tinyMCE.editors );
		SobiPro.jQuery.each( Editors, function ( i, eid )
		{
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
    try {
        SobiPro.jQuery.each( Joomla.editors.instances, function ()
        {
            try {
                this.toTextArea();
            }
            catch ( e ) {
            }
        } );
    }
    catch ( e ) {
    }
    try {
        SobiPro.jQuery.each( Joomla.editors.instances, function ( i, e )
        {
            e.save();
        } )
    }
    catch ( e ) {
    }
}
