/**
 * @version: $Id: field_category_tree.js 4342 2014-10-22 09:05:14Z Radek Suski $
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

 * $Date: 2014-10-22 11:05:14 +0200 (Wed, 22 Oct 2014) $
 * $Revision: 4342 $
 * $Author: Radek Suski , Marcos A. Rodríguez Roldán $
 * $
 * File location: components/com_sobipro/lib/js/opt/field_category_tree.js $
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
			e.preventDefault();
			proxy.getCategoryData( SobiPro.jQuery( this ).attr( 'rel' ) );
		
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
			var wait = '<i class="icon-spinner icon-spin"></i>&nbsp;&nbsp;';
			button.attr( 'disabled', 'disabled' );
			button.html( wait + this.addBtn );
			proxy.canvas.find( '.sigsiuTree' )
				.addClass( 'disabledArea' );
			return setTimeout( function ()
			{
				//proxy.addCategory();
			}, 3000 );
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
		

	/*

for(a=0;a<formularios1.option.length;a++){
formularios2.options[a].value = formularios1.options[a].value;
formularios2.options[a].text = formularios1.options[a].text;
}*/   // pasar_parametro('field_category_childsContainer', 'field_category_list[]')
	var clon = proxy.canvas.find('.childsContainer option:selected');
	var selected1 = proxy.selectField.find( 'option' );

		if ( selected1.length  ) {
		selected1.each( function ( a, option2 )
			{	
		clon.each( function ( e, option )
			{
						//alert('clom:'+ option.value +'\n selec:'+option2.value )
						if(option.value == option2.value){
							
							/*clon.attr('disabled','disabled');
	clon.removeAttr( 'selected', 'selected' );*/
	SobiPro.Alert( 'THIS_CATEGORY_HAS_BEEN_ALREADY_ADDED' );
	stopadd();	
							}
			} );} );
		}

	if(!clon.length){SobiPro.Alert( 'PLEASE_SELECT_CATEGORY_YOU_WANT_TO_ADD_IN_THE_TREE_FIRST' );
	/*if ( proxy.settings.preventParents && proxy.category.childsCount > 0 ) {
			SobiPro.Alert( 'SELECT_CAT_WITH_NO_CHILDS' );
			
		}*/}
	clon.clone().appendTo(proxy.selectField);
	clon.attr('disabled','disabled');
	clon.removeAttr( 'selected', 'selected' );
	//clon.attr('style','color:#D5D5D5;');
		proxy.canvas.find( '[name="removeCategory"]' ).removeAttr( 'disabled', 'disabled' );
			proxy.updateSelected();
	} );

	this.canvas.find( '[name="removeCategory"]' ).click( function ()
	{
		var selected = proxy.selectField.find( 'option:selected' );
		if ( selected.length ) {
			selected.each( function ( i, option )
			{
				//var disaremo = $('.field_category_childsContainer option[value="' + option.value+']')
				var disaremo = SobiPro.jQuery('.childsContainer option[value="' + option.value+'"]')
			
				disaremo.removeAttr( 'disabled', 'disabled' );
				//disaremo.removeAttr( 'style' );
				SobiPro.jQuery( option ).remove()
				 //selected.remove().appendTo('.field_category_childsContainer'); 
			} );
			proxy.selectField.removeAttr( 'readonly', 'readonly' );
			proxy.canvas.find( '[name="addCategory"]' ).removeAttr( 'disabled', 'disabled' );
		}if ( !selected.length ){SobiPro.Alert( 'PLEASE_SELECT_CATEGORY_YOU_WANT_TO_REMOVE')}
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


