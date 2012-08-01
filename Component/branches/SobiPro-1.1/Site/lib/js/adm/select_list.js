 /**
 * @version: $Id: select_list.js 1616 2011-07-07 12:10:31Z Radek Suski $
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
 * $Date: 2011-07-07 14:10:31 +0200 (Thu, 07 Jul 2011) $
 * $Revision: 1616 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/js/adm/select_list.js $
 */
var SPsortables = new Array();
function SPchangeHandle( list, handle )
{
	$( list ).getElements( 'li' ).each( function( li ) {
		li.getElements( handle ).each( function( sp ) { sp.cloneEvents( li ); } );
		li.removeEvents();
	} );
}

function SPaddOpt( node )
{
	listId = node.parentNode.parentNode.id;
	el = ( listId == 'spOptions0' ) ? $( 'spOptionsDummy' ).children[ 0 ] :  $( 'spOptionsDummy' ).getElements( '.sspOption' )[ 0 ];
	li = el.cloneNode( true );
	// In the stupid IE it does not work of course 
	// Not the getElements() nor each()
//	li.getElements( 'input' ).each( function( box ) { 
//		box.name = box.name.replace( /__/g, 'field' ); 
//		if( box.name.indexOf( 'id' ) != -1 ) {
//			box.value = box.value.replace( /\d/g, ++SPoptCount );
//		}
//	} );
	inputs = li.getElementsByTagName( 'input' );
	for( var i = 0; i < inputs.length; i++ ) {
		box = inputs[ i ];
		box.name = box.name.replace( /__/g, 'field' );
		if( box.name.indexOf( 'id' ) != -1 ) {
			box.value = box.value.replace( /\d/g, ++SPoptCount );
		}
	}
	$( listId ).insertBefore( li, $( listId ).children[ 1 ] );
	index = listId.replace( /[^\d]/g, '' );
	SPsortables[ index ].detach();
	SPsortables[ index ] = new Sortables( listId, { onComplete: function( el ) {  SPresortOpts( el ); } } );
	handle = index == 0 ? '.SPhandle' : '.sSPhandle';
	li.getElements( '.SPOptDel' ).each( function( bt ) {
		bt.removeEvents();
		bt.addEvent( 'click', function() { SPdelOpt( this ); } );
	} );
	SPchangeHandle( listId, handle );
	SPresortOpts( li );
}

function SPresortOpts( el )
{	
	if( el.className == 'spOption' || el.className == 'spOptionGroup' ) {
		index = 0;
		el.parentNode.getElements( 'li' ).each( function( li ) {
			if( li.className == 'spOption' || li.className == 'spOptionGroup' ) {
				index++;
				li.getElements( 'input' ).each( function( box ) { box.name = box.name.replace( /\d+/, index );  } );
			}			
		} );
	}
	else if( el.className == 'sspOption' ) {
		index2 = 0;
		index = el.parentNode.parentNode.parentNode.getElements( '.spOptionContent' )[ 0 ].getElements( 'input' )[ 0 ].name.replace( /[^\d]/g, '' );
		el.parentNode.getElements( '.sspOption' ).each( function( li ) {
			if( li.className == 'sspOption' ) {
				index2++;
				li.getElements( 'input' ).each( function( box ) { 
					c = box.name;
					box.name = box.name.replace( /\[\d+\]\[\d+\]/, '[' + index + '][' + index2 + ']' );
				} );				 
			}
		} );
	}
}

function SPdelOpt( node )
{
	var el = node.parentNode;
	if( ( el.className != 'spOptionGroup' ) || ( confirm( SobiPro.Txt(  'GRP_DEL_WARN' ) ) ) ) {
		SPsortables[ el.parentNode.id.replace( /[^\d]/g, '' ) ].elements.push( el );
		el.parentNode.removeChild( el );
	}
}

window.addEvent( 'domready', function() {
	SPsortables[ 0 ] = new Sortables( 'spOptions0', { onComplete: function( el ) {  SPresortOpts( el ); } } );
	SPchangeHandle( 'spOptions0', '.SPhandle' );
	$$( '.SPOptDel' ).addEvent( 'click', function() { SPdelOpt( this ); } );
	$$( '.SPnewOpt' ).addEvent( 'click', function() { SPaddOpt( this ); } );
	if( $( 'SPnewOptGr' ) )  {
		$( 'SPnewOptGr' ).addEvent( 'click', function() {
			id = 'spOptions' + SPsortables.length;
			el = $( 'spOptionsDummy' ).children[ 1 ];
			li = el.cloneNode( true );
			// In the stupid IE it does not work of course 
			// Not the getElements() nor each()			
//			li.getElements( 'input' ).each( function( box ) { 
//				box.name = box.name.replace( /__/g, 'field' ); 
//				if( box.name.indexOf( 'id' ) != -1 ) {
//					box.value = box.value.replace( /\d/g, ++SPoptCount );
//				}
//			} );
			inputs = li.getElementsByTagName( 'input' );
			for( var i = 0; i < inputs.length; i++ ) {
				box = inputs[ i ];
				box.name = box.name.replace( /__/g, 'field' ); 
				if( box.name.indexOf( 'id' ) != -1 ) {
					box.value = box.value.replace( /\d/g, ++SPoptCount );
				}
			}			
			li.getElementsByTagName( 'ul' )[ 0 ].id = id;
			var ni = SP_class( '.SPnewOpt', li );
			for( var i = 0; i < ni.length; i++ ) {
				bt = ni[ i ];
				bt.addEvent( 'click', function() { SPaddOpt( this ); } ); 
			}
//			li.getElements( '.SPnewOpt' ).each( function( bt ) { bt.addEvent( 'click', function() { SPaddOpt( this ); } ); } );
			$( 'spOptions0' ).insertBefore( li, $( 'spOptions0' ).children[ 1 ] );
			SPsortables[ 0 ] = new Sortables( 'spOptions0', { onComplete: function( el ) {  SPresortOpts( el ); } } );
			SPresortOpts( li );
			SPsortables[ SPsortables.length ] = new Sortables( id, { onComplete: function( el ) {  SPresortOpts( el ); } } );
			SPchangeHandle( id, '.sSPhandle' );
			li.getElements( '.SPOptDel' ).each( function( bt ) {
				bt.removeEvents();
				bt.addEvent( 'click', function() { SPdelOpt( this ); } );
			} );
			SPchangeHandle( 'spOptions0', '.SPhandle' );
		} );
	}
});