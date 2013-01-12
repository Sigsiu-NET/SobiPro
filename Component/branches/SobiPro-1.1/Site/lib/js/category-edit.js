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
	this.options = this.canvas.find( '.selected' ).find( 'select option' );
	this.selectField = this.canvas.find( '.selected' ).find( 'select' );
	this.loading = false;
	this.addBtn = '';

	this.getCategoryData = function ( sid )
	{
		this.loading = true;
		SobiPro.jQuery.ajax( {
			'url':'index.php',
			'data':{'option':'com_sobipro', 'task':'category.parents', 'sid':sid, 'out':'json'},
			'type':'post',
			'dataType':'json',
			success:function ( data )
			{
				data.categories.each( function ( category )
				{
					proxy.category = category;
				} );
				proxy.loading = false;
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

	this.updateSelected = function ()
	{
		var selectedCats = [];
		this.canvas.find( '.selected' ).find( 'select option' ).each( function ( i, e )
		{
			selectedCats.push( SobiPro.jQuery( e ).val() )
		} );
		this.canvas.find( '.selected' ).find( 'input' ).val( 'json://[' + selectedCats.join( ',' ) + ']' );
	}

	this.addCategory = function ( e )
	{
		var button = this.canvas.find( '[name="addCategory"]' ).attr( 'disabled', 'disabled' );
		var error = false;
		if ( this.addBtn.length ) {
			button.html( this.addBtn );
			button.removeAttr( 'disabled' );
			this.addBtn = '';
		}
		if ( proxy.loading ) {
			this.addBtn = button.html();
			var wait = '<i class="icon-spinner icon-spin"></i>&nbsp;&nbsp;';
			button.attr( 'disabled', 'disabled' );
			button.html( wait + this.addBtn );
			return setTimeout( function ()
			{
				proxy.addCategory()
			}, 3000 );
		}
		var selector = this;
		if ( !( proxy.category.id ) ) {
			SobiPro.Alert( 'PLEASE_SELECT_CATEGORY_YOU_WANT_TO_ADD_IN_THE_TREE_FIRST' );
			error = true;
		}
		this.canvas.find( '.selected' ).find( 'select option' ).each( function ( i, option )
		{
			if ( proxy.category.id == SobiPro.jQuery( option ).val() ) {
				SobiPro.Alert( 'THIS_CATEGORY_HAS_BEEN_ALREADY_ADDED' );
				error = true;
			}
		} );
		if ( !( error ) ) {
			this.selectField.append( new Option( SobiPro.StripSlashes( this.category.name ), this.category.id ) );
			this.canvas.find( '[name="removeCategory"]' ).removeAttr( 'disabled', 'disabled' );
			if ( this.canvas.find( '.selected' ).find( 'select option' ).length >= this.settings.maxcats ) {
				this.selectField.attr( 'readonly', 'readonly' );
				button.attr( 'disabled', 'disabled' );
			}
			this.updateSelected();
		}
	}

	this.canvas.find( '[name="addCategory"]' ).click( function ( e )
	{
		proxy.addCategory( e );
	} );

	this.canvas.find( '[name="removeCategory"]' ).click( function ()
	{
		var selected = proxy.selectField.find( 'option:selected' );
		if ( selected.length ) {
			selected.each( function ( i, option )
			{
				SobiPro.jQuery( option ).remove()
			} );
			proxy.selectField.removeAttr( 'readonly', 'readonly' );
			proxy.canvas.find( '[name="addCategory"]' ).removeAttr( 'disabled', 'disabled' );
		}
		if ( !( proxy.canvas.find( '.selected' ).find( 'select option' ).length ) ) {
			SobiPro.jQuery( this ).attr( 'disabled', 'disabled' );
		}
		proxy.updateSelected();
	} );

	try {
		SobiPro.jQuery( '#' + this.settings.field + '_modal' ).click(
			function() {
//				SobiPro.jQuery( '#' + proxy.settings.field + '_modal' ).css( 'display', '');
			}
		);
	} catch ( e ) {}
}
