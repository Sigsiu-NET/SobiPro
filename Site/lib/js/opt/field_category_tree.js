/**
 * @package: SobiPro Library

 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: https://www.Sigsiu.NET

 * @copyright Copyright (C) 2006 - 2015 Sigsiu.NET GmbH (https://www.sigsiu.net). All rights reserved.
 * @license GNU/LGPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License version 3
 * as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/lgpl.html and https://www.sigsiu.net/licenses.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
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
	this.requested = false;
	this.addBtn = '';
	this.rel = 0;

	this.getCategoryData = function ( sid )
	{
		this.loading = true;
		SobiPro.jQuery.ajax( {
			'url': 'index.php',
			'data': {'option': 'com_sobipro', 'task': 'category.parents', 'sid': sid, 'out': 'json'},
			'type': 'post',
			'dataType': 'json',
			success: function ( data )
			{
				SobiPro.jQuery.each( data.categories, function ( i, category )
				{
					proxy.category = category;
				} );
				proxy.loading = false;
			}
		} );
	};

	this.init = function ()
	{
		this.canvas.find( '.treeNode' ).click( function ( e )
		{
			SobiPro.jQuery( this ).focus();
			var Rel = SobiPro.jQuery( this ).attr( 'rel' );
			if ( proxy.rel != Rel ) {
				proxy.rel = Rel;
				e.preventDefault();
				proxy.getCategoryData( SobiPro.jQuery( this ).attr( 'rel' ) );
			}
		} );

	};

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
			SobiPro.jQuery( e ).prop( 'selected', true );
			selectedCats.push( SobiPro.jQuery( e ).val() )
		} );
		this.canvas.find( '.selected' ).find( 'input' ).val( 'json://[' + selectedCats.join( ',' ) + ']' );
	};

	this.addCategory = function ()
	{
		var button = this.canvas.find( '[name="addCategory"]' );
		var error = false;
		if ( this.addBtn.length ) {
			button.html( this.addBtn );
			button.removeAttr( 'disabled' );
			this.addBtn = '';
		}
		if ( proxy.loading ) {
			this.addBtn = button.html();
			var wait = '<i class="' + SobiPro.Ico( 'category-field.spinner', 'icon-spinner icon-spin' ) + '"></i>&nbsp;&nbsp;';
			button.attr( 'disabled', 'disabled' );
			button.html( wait + this.addBtn );
			proxy.canvas.find( '.sigsiuTree' )
				.addClass( 'disabledArea' );
			return setTimeout( function ()
			{
				proxy.addCategory();
			}, 1000 );
		}
		if ( !( proxy.category.id ) ) {
			SobiPro.Alert( 'PLEASE_SELECT_CATEGORY_YOU_WANT_TO_ADD_IN_THE_TREE_FIRST' );
			error = true;
		}

		if ( proxy.settings.preventParents && proxy.category.childsCount > 0 ) {
			SobiPro.Alert( 'SELECT_CAT_WITH_NO_CHILDS' );
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
			proxy.canvas.find( '.sigsiuTree' ).removeClass( 'disabledArea' );
			if ( this.canvas.find( '.selected' ).find( 'select option' ).length >= this.settings.maxcats ) {
				this.selectField.attr( 'readonly', 'readonly' );
				button.attr( 'disabled', 'disabled' );
			}
			this.updateSelected();
		}

	};

	this.canvas.find( '[name="addCategory"]' ).click( function ()
	{
		proxy.addCategory();
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
			function ()
			{
//				SobiPro.jQuery( '#' + proxy.settings.field + '_modal' ).css( 'display', '');
			}
		);
	}
	catch ( e ) {
	}
}
