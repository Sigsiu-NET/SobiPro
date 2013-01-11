/**
 * @version: $Id: edit.js 992 2011-03-17 16:31:33Z Radek Suski $
 * @package: SobiPro Library
 * ===================================================
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET
 * ===================================================
 * @copyright Copyright (C) 2006 - 2011 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license see http://www.gnu.org/licenses/lgpl.html GNU/LGPL Version 3.
 * You can use, redistribute this file and/or modify it under the terms of the GNU Lesser General Public License version 3
 * ===================================================
 * $Date: 2011-03-17 17:31:33 +0100 (Thu, 17 Mar 2011) $
 * $Revision: 992 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/js/edit.js $
 */

function SigsiuTreeEdit( options )
{
	var proxy = this;
	this.settings = options;
	this.canvas = SobiPro.jQuery( '#' + this.settings.field + '_canvas' );
	this.category = {};
	this.options = proxy.canvas.find( '.selected' ).find( 'select option' );
	this.select = proxy.canvas.find( '.selected' ).find( 'select' );
	this.getCategoryData = function ( sid )
	{
		SobiPro.jQuery.ajax( {
			url:'index.php',
			data:{'option':'com_sobipro', 'task':'category.parents', 'sid':sid, 'out':'json'},
			type:'post',
			dataType:'json',
			success:function ( data )
			{
				data.categories.each( function ( category )
				{
					proxy.category = category;
				} );
			}
		} );
	}
	this.init = function ()
	{
		this.canvas.find( '.treeNode' ).click( function ( e )
		{
			SobiPro.jQuery( this ).focus();
			e.preventDefault();
			proxy.getCategoryData( SobiPro.jQuery( this ).attr( 'rel' ) );
		} );

	}

	this.canvas.find( '.tree' ).bind( 'DOMNodeInserted', function ()
	{
		proxy.init();
	} );

	this.init();
	this.canvas.find( '[name="addCategory"]' ).click( function ()
	{
		var selector = this;
		if ( !( proxy.category.id ) ) {
			SobiPro.Alert( 'PLEASE_SELECT_CATEGORY_YOU_WANT_TO_ADD_IN_THE_TREE_FIRST' );
			return false;
		}
		proxy.canvas.find( '.selected' ).find( 'select option' ).each( function ( i, option )
		{
			if ( proxy.category.id == SobiPro.jQuery( option ).val() ) {
				SobiPro.Alert( 'THIS_CATEGORY_HAS_BEEN_ALREADY_ADDED' );
				return false;
			}
		} );
		proxy.select.append( new Option( SobiPro.StripSlashes( proxy.category.name ), proxy.category.id ) );
		proxy.canvas.find( '[name="removeCategory"]' ).removeAttr( 'disabled', 'disabled' );
		if ( proxy.canvas.find( '.selected' ).find( 'select option' ).length >= proxy.settings.maxcats ) {
			proxy.select.attr( 'readonly', 'readonly' );
			SobiPro.jQuery( selector ).attr( 'disabled', 'disabled' );
		}
	} );

	this.canvas.find( '[name="removeCategory"]' ).click( function ()
	{
		var selected = proxy.select.find( 'option:selected' );
		if ( selected.length ) {
			selected.each( function ( i, option )
			{
				SobiPro.jQuery( option ).remove()
			} );
			proxy.select.removeAttr( 'readonly', 'readonly' );
			proxy.canvas.find( '[name="addCategory"]' ).removeAttr( 'disabled', 'disabled' );
		}
		if ( !( proxy.canvas.find( '.selected' ).find( 'select option' ).length ) ) {
			SobiPro.jQuery( this ).attr( 'disabled', 'disabled' );
		}
	} );
}

window.addEvent( 'domready', function ()
{
	$( 'spEntryForm' ).addEvent( 'submit', function ( ev )
	{
		if ( $( 'SP_task' ) == 'cancel' ) {
			return true;
		}
		else if ( !( SPValidate( $( 'spEntryForm' ) ) ) ) {
			new Event( ev ).stop();
			return false;
		}
	} );
} );
