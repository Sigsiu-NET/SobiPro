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

var selectedCat = 0;
var selectedCatName = '';
var selectedCats = new Array();
var selectedCatNames = new Array();
var selectedPath = '';
var selCid = 0;
var SPObjType = 'entry';
var maxCat = '__MAXCATS__';

SobiPro.onReady( function () {
	var Cinit = new String( SP_id( 'entry.parent' ).value );
	if( Cinit != '' ) {
		var cats = Cinit.split( ',' );
		for( var i = 0; i < cats.length; i++ ) {
			if( cats[ i ] > 0 ) {
				SP_selectCat( cats[ i ], 1 );
			}
		}
	}
} );

function SP_selectCat( sid, add )
{
	var separator = "__SEPARATOR__";
	var node = SP_id( 'sobiCatsstNode' + sid );
	var cats = new Array();
	// fix for chrome
	try {
		SP_id( 'sobiCats_CatUrl' + sid ).focus();
	} catch( e ) {}

	var request = new SobiPro.Json(
		"__URL__" + '&sid=' + sid,
		{
			onComplete: function( jsonObj, jsons )
			{
				selectedCat = sid;
		        jsonObj.categories.each(
	        		function( cat )
	        		{
	        			cats[ cat.id ] = cat.name;
	        			selectedCatName = cat.name;
	        		}
		        );
		        selectedPath = cats.join( separator );
		        if( add == 1 ) {
		        	SP_addCat();
			    }
			}
		}
	).send();
}

function SP_Save()
{
	SP_id( 'entry.path' ).value = SobiPro.StripSlashes( selectedCatNames.join( '\n' ) );
	SP_id( 'entry.parent' ).value = selectedCats.join( ', ' );
}

function SP_addCat()
{
	if( selectedCat == 0 || selectedPath == '' ) {
		SobiPro.Alert( "PLEASE_SELECT_CATEGORY_YOU_WANT_TO_ADD_IN_THE_TREE_FIRST" );
		return false;
	}
	for( var i = 0; i <= selectedCats.length; ++i ) {
		if( selectedCats[ i ] == selectedCat ) {
			SobiPro.Alert( "THIS_CATEGORY_HAS_BEEN_ALREADY_ADDED" );
			return false;
		}
	}
	var selCats = SP_id( 'selectedCats' );
	var newOpt = document.createElement( 'option' );
	newOpt.text = SobiPro.StripSlashes( selectedCatName );
	newOpt.value = selectedCat;
	newOpt.title = SobiPro.StripSlashes( selectedPath );
    try { selCats.add( newOpt, null ); } catch( x ) { selCats.add( newOpt ); }
    selectedCatNames[ selectedCats.length ] = selectedPath;
    selectedCats[ selectedCats.length ] = selectedCat;
    for ( var i = 0; i <= selCats.options.length; ++i ) {
	    if( i >=  maxCat ) {
	    	SP_id( 'SpTreeAddButton' ).disabled = true;
	    	break;
		}
    }
}

function SP_delCat()
{
	var selCats = SP_id( 'selectedCats' );
	var selOpt = selCats.options[ selCats.selectedIndex ];
	cid = selOpt.value;
	cp =  selOpt.title;
	selCats.options[ selCats.selectedIndex ] = null;
	for( var i = 0; i <= selectedCats.length; ++i ) {
		if( selectedCats[ i ] == cid ) {
			selectedCatNames.splice( i, 1 );
			selectedCats.splice( i, 1 );
		}
	}
	SP_id( 'SpTreeAddButton' ).disabled = false;
}

window.addEvent( 'domready', function() {
	$( 'spEntryForm' ).addEvent( 'submit', function( ev ) {
		if( $( 'SP_task' ) == 'cancel' ) {
			return true;
		}
		else if( !( SPValidate( $( 'spEntryForm' ) ) ) ) {
			new Event( ev ).stop();
			return false;
		}
	} );
} );
