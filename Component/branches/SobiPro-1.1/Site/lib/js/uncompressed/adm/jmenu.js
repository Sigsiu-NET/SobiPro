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
	SPJoomlaMenu();
} );

function SPJmenuFixTask( value )
{
	SobiPro.jQuery( document ).ready( function ()
	{
		try {
			SobiPro.jQuery( SobiPro.jQuery( '#jform_type-lbl' ).siblings()[ 0 ] ).val( value );
		}
		catch ( e ) {
		}
		try {
			var lType = SobiPro.jQuery( '[name*="jform[type]"]' ).parent().find( 'input[type=text]' );
			lType.val( value );
			lType.css( 'min-width', '200px' );
			SobiPro.jQuery( '#jform_link' ).css( 'min-width', '500px' );
		}
		catch ( e ) {
		}
	} );
}

function SPJoomlaMenu()
{
	try {
		var JSubmit = Joomla.submitbutton;
		Joomla.submitbutton = function ( pressbutton, type )
		{
			if ( pressbutton.indexOf( 'save' ).indexOf == -1 || pressbutton.indexOf( 'apply' ) == -1 || SPValidate() ) {
				JSubmit( pressbutton, type );
			}
		}
	}
	catch ( x ) {
		var JSubmit = submitbutton;
		submitbutton = function ( pressbutton, type )
		{
			if ( pressbutton.indexOf( 'save' ).indexOf == -1 || pressbutton.indexOf( 'apply' ) == -1 || SPValidate() ) {
				JSubmit( pressbutton, type );
			}
		}
	}
	SobiPro.jQuery( "#spsection" ).bind( "change", function ()
	{
		if ( !( SobiPro.jQuery( "#spsection option:selected" ).val() ) ) {
			return;
		}
		sid = SobiPro.jQuery( "#spsection option:selected" ).val();
		SobiPro.jQuery( "#sid" ).val( sid );
		SPSetObjectType( SPJmenuStrings.objects.section );
		SobiPro.jQuery( "#oname" ).val( SobiPro.htmlEntities( SobiPro.jQuery( "#spsection option:selected" ).html() ) );
		SobiPro.jQuery( "#sp_category" )
			.removeClass( 'btn-primary' )
			.html( SPJmenuStrings.labels.category );
		SobiPro.jQuery( "#sp_entry" )
			.removeClass( 'btn-primary' )
			.html( SPJmenuStrings.labels.entry );
		SPReloadTemplates( 'section' );
	} );
	if ( SobiPro.jQuery( "#sp_category" ) != null ) {
		SobiPro.jQuery( "#sp_category" ).bind( "click", function ( e )
		{
			if ( SobiPro.jQuery( "#sid" ).val() == 0 ) {
				SobiPro.Alert( "PLEASE_SELECT_SECTION_FIRST" );
				semaphore = 0;
				return false;
			}
			else {
				var requestUrl = SobiProUrl.replace( '%task%', 'category.chooser' ) + '&treetpl=rchooser&multiple=1&tmpl=component&sid=' + SobiPro.jQuery( "#sid" ).val();
				jQuery( "#spCatsChooser" ).html( '<iframe id="spCatSelectFrame" src="' + requestUrl + '" style="width: 480px; height: 400px; border: none;"> </iframe>' );
				SobiPro.jQuery( '#spCat' ).modal();
				semaphore = 0;
			}
		} );
	}
	if ( SobiPro.jQuery( "#sp_entry" ) != null ) {
		SobiPro.jQuery( "#sp_entry" ).bind( "click", function ( e )
			{
				if ( SobiPro.jQuery( "#sid" ).val() == 0 ) {
					SobiPro.Alert( "PLEASE_SELECT_SECTION_FIRST" );
					return false;
				}
				else {
					//var requestUrl = SobiProUrl.replace( '%task%', 'entry.search' ) + '&sid=' + ;
					SobiPro.jQuery( '#spEntryChooser' ).typeahead( {
						source: function ( typeahead, query )
						{
							var request = {
								'option': 'com_sobipro',
								'task': 'entry.search',
								'sid': SobiPro.jQuery( "#sid" ).val(),
								'search': query,
								'format': 'raw'
							};
							return SobiPro.jQuery.ajax( {
								'type': 'post',
								'url': 'index.php',
								'data': request,
								'dataType': 'json',
								success: function ( response )
								{
									responseData = [];
									if ( response.length ) {
										for ( var i = 0; i < response.length; i++ ) {
											responseData[ i ] = { 'name': response[ i ].name + ' ( ' + response[ i ].id + ' )', 'id': response[ i ].id, 'title': response[ i ].name  };
										}
										typeahead.process( responseData );
										SobiPro.jQuery( '.typeahead' )
											.addClass( 'typeahead-width' )
											.css( 'font-size', '13px' )
										;
										SobiPro.jQuery( '#spEntryChooser' ).after( SobiPro.jQuery( '.typeahead' ) );
									}
								}
							} );
						},
						onselect: function ( obj )
						{
							SobiPro.jQuery( '#selectedEntry' ).val( obj.id );
							SobiPro.jQuery( '#selectedEntryName' ).val( obj.title )
						},
						property: "name"
					} );
					SobiPro.jQuery( '#spEntry' ).modal();
				}
			}
		)
		;
	}
	SobiPro.jQuery( '#spEntrySelect' ).bind( "click", function ( e )
	{
		if ( !( SobiPro.jQuery( '#selectedEntry' ).val() ) ) {
			return;
		}
		SobiPro.jQuery( '#sid' ).val( SobiPro.jQuery( '#selectedEntry' ).val() );
		SPSetObjectType( SPJmenuStrings.objects.entry );
		SobiPro.jQuery( "#sp_entry" )
			.addClass( 'btn-primary' )
			.html( SobiPro.htmlEntities( SobiPro.jQuery( '#selectedEntryName' ).val() ) );
		SobiPro.jQuery( "#sp_category" )
			.removeClass( 'btn-primary' )
			.html( SPJmenuStrings.labels.category );
		SPReloadTemplates( 'entry' );

	} );
	SobiPro.jQuery( '#spCatSelect' ).bind( "click", function ( e )
	{
		if ( !( SobiPro.jQuery( '#selectedCat' ).val() ) ) {
			return;
		}
		SobiPro.jQuery( '#sid' ).val( SobiPro.jQuery( '#selectedCat' ).val() );
		SPSetObjectType( SPJmenuStrings.objects.category );
		SobiPro.jQuery( "#sp_category" )
			.addClass( 'btn-primary' )
			.html( SobiPro.htmlEntities( SobiPro.jQuery( '#selectedCatName' ).val() ) );
		SobiPro.jQuery( "#sp_entry" )
			.removeClass( 'btn-primary' )
			.html( SPJmenuStrings.labels.entry );
		SPReloadTemplates( 'category' );
	} );
	SobiPro.jQuery( '#sptpl' ).change( function ()
	{
		if ( SobiPro.jQuery( this ).find( 'option:selected' ).val() ) {
			SobiPro.jQuery( this ).attr( 'name', SobiPro.jQuery( this ).attr( 'name' ).replace( '-sptpl-', 'sptpl' ) );
		}
		else {
			SobiPro.jQuery( this ).attr( 'name', SobiPro.jQuery( this ).attr( 'name' ).replace( 'sptpl', '-sptpl-' ) );
			SobiPro.jQuery( '#jform_link' ).val( SobiPro.jQuery( '#jform_link' ).val().replace( /\&sptpl\=[a-zA-Z0-9\-\_\.]*/gi, '' ) )
		}
	} );
	SobiPro.jQuery( '.SobiProCalendar' ).find( 'select' ).change( function ()
		{
			"use strict";
			var date = [];
			SobiPro.jQuery( '.SobiProCalendar' ).find( 'select' ).each( function ( i, e )
			{
				if ( SobiPro.jQuery( this ).val() ) {
					date.push( SobiPro.jQuery( this ).val() );
				}
			} );
			SobiPro.jQuery( '#selectedDate' ).val( date.join( '.' ) );
		}
	);
}

function SPSetObjectType( type )
{
	if ( !( SPJmenuStrings.task ) ) {
		SobiPro.jQuery( "#otype" ).val( type );
	}
}
function SPReloadTemplates( type )
{
	if ( ( SPJmenuStrings.task ) ) {
		type = SPJmenuStrings.task;
	}
	sid = SobiPro.jQuery( "#spsection option:selected" ).val();
	var request = {
		'option': 'com_sobipro',
		'task': 'template.list',
		'sid': sid,
		'type': type,
		'format': 'raw'
	};
	SobiPro.jQuery.ajax( {
		'type': 'post',
		'url': 'index.php',
		'data': request,
		'dataType': 'json',
		success: function ( response )
		{
			responseData = [];
			SobiPro.jQuery( "#sptpl option" ).each( function ()
			{
				if ( SobiPro.jQuery( this ).val() ) {
					SobiPro.jQuery( this ).remove();
				}
			} );
			if ( response.length ) {
				for ( var i = 0; i < response.length; i++ ) {
					SobiPro.jQuery( "#sptpl" ).append( '<option value="' + response[ i ].name + '">' + response[ i ].filename + '</option>' );
				}
			}
		}
	} );
}

function SPValidate()
{
	if ( SobiPro.jQuery( "#sid" ).val() == 0 || SobiPro.jQuery( "#sid" ).val() == "" ) {
		SobiPro.Alert( 'YOU_HAVE_TO_AT_LEAST_SELECT_A_SECTION' );
		return false;
	}
	else {
		return true;
	}
}
