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
 // Created at __CREATED__ by Sobi Pro Component


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
