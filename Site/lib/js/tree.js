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

var __ID___stmcid = 0;
var __ID___stmLastNode = __LAST_NODE__;
var __ID___stmImgs = [];
var __ID___stmImgMatrix = [];
var __ID___stmParents = [];
var __ID___stmSemaphor = 0;
var __ID___stmPid = 0;
var __ID___stmWait = '__SPINNER__';
__IMAGES_ARR__;
__IMAGES_MATRIX__;
//__PARENT_ARR__

function __ID___stmExpand( catid, deep, pid )
{
	try { SP_id( "__ID___imgFolder" + catid ).src = __ID___stmWait; } catch( e ) {}
	__ID___stmcid = catid;
	__ID___stmPid = pid;
	url = "__URL__";
	__ID___stmMakeRequest( url, deep, catid );
}

function __ID___stmCatData( node, val )
{
	return node.getElementsByTagName( val ).item( 0 ).firstChild.data;
}

function __ID___stmAddSubcats( XMLDoc, deep, ccatid )
{
	var categories = XMLDoc.getElementsByTagName( 'category' );
	var subcats = "";
	deep++;
	for( i = 0; i < categories.length; i++ ) {
		var category 	= categories[ i ];
		var catid 		= __ID___stmCatData( category, 'catid' );
		var name 		= __ID___stmCatData( category, 'name' );
		var introtext 	= __ID___stmCatData( category, 'introtext' );
		var parentid 	= __ID___stmCatData( category, 'parentid' );
		var url 		= __ID___stmCatData( category, 'url' );
		var childs 		= __ID___stmCatData( category, 'childs' );
		var join 		= "<img src='" + __ID___stmImgs['join'] + "' alt=''/>";
		var margin 		= "";
		var childContainer = "";
		name 			= name.replace( "\\", "" );
		introtext 		= introtext.replace( "\\", "" );
		url 			= url.replace( "\\\\", "" );

		for( j = 0; j < deep; j++ ) {
			if( __ID___stmImgMatrix[ parentid ][ j ] ) {
				switch( __ID___stmImgMatrix[ parentid ][ j ] )
				{
					case 'plus':
					case 'minus':
					case 'line':
						image = 'line';
						break;
					default:
						image = 'empty';
						break;
				}
			}
			else {
				image = 'empty';
			}
			if( !__ID___stmImgMatrix[ catid ] ) {
				catArray = [];
				catArray[ j ]  = image;
				__ID___stmImgMatrix[ catid ] = catArray;
			}
			else {
				__ID___stmImgMatrix[ catid ][ j ] = image;
			}
			margin = margin + "<img src='"+ __ID___stmImgs[ image ] +"' style='border-style:none;' alt=''/>";
		}
		if( childs > 0 ) {
			join = "<a href='javascript:__ID___stmExpand( " + catid + ", " + deep + ", " + __ID___stmPid + " );' id='__ID___imgUrlExpand" + catid + "'><img src='"+ __ID___stmImgs['plus'] + "' id='__ID___imgExpand" + catid + "'  style='border-style:none;' alt='expand'/></a>";
			__ID___stmImgMatrix[catid][j] = 'plus';
		}
		if( __ID___stmcid == __ID___stmLastNode ) {
			line = "<img src='"+__ID___stmImgs['empty']+"' alt=''>";
		}
		if( i == categories.length - 1 ) {
			if( childs > 0 ) {
				join = "<a href='javascript:__ID___stmExpand( " + catid + ", " + deep + ", " + __ID___stmPid + " );' id='__ID___imgUrlExpand" + catid + "'><img src='"+ __ID___stmImgs[ 'plusBottom' ] + "' id='__ID___imgExpand" + catid + "'  style='border-style:none;' alt='expand'/></a>";
				__ID___stmImgMatrix[ catid ][ j ] = 'plusBottom';
			}
			else {
				join = "<img src='" + __ID___stmImgs[ 'joinBottom' ] + "' style='border-style:none;' alt=''/>";
				__ID___stmImgMatrix[ catid ][ j ] = 'joinBottom';
			}
		}
		subcats = subcats + "<div class='sigsiuTreeNode' id='__ID__stNode" + catid + "'>" + margin  + join + "<a id='__ID__" + catid + "' href=\"" + url + "\"><img src='" + __ID___stmImgs[ 'folder' ] + "' id='__ID___imgFolder" + catid + "' alt=''></a><a class='treeNode' rel=\""+ catid + "\" id='__ID___CatUrl" + catid + "' href=\"" + url + "\">" + name + "</a></div>";
		if( childs > 0 ) {
			subcats = subcats + "<__TAG__ class='clip' id='__ID___childsContainer" + catid + "' style='display: block;  display:none;'></div>"
		}
	}
	var childsCont = "__ID___childsContainer" + ccatid;
	SP_id( childsCont ).innerHTML = subcats;
}

function __ID___stmMakeRequest( url, deep, catid )
{
	var __ID___stmHttpRequest;
    if ( window.XMLHttpRequest ) {
        __ID___stmHttpRequest = new XMLHttpRequest();
        if ( __ID___stmHttpRequest.overrideMimeType ) {
            __ID___stmHttpRequest.overrideMimeType( 'text/xml' );
        }
    }
    else if ( window.ActiveXObject ) {
        try { __ID___stmHttpRequest = new ActiveXObject( "Msxml2.XMLHTTP" ); }
        catch ( e ) { try { __ID___stmHttpRequest = new ActiveXObject("Microsoft.XMLHTTP"); } catch (e) {} }
    }
    if ( !__ID___stmHttpRequest ) {
//        alert( '__FAIL_MSG__' );
        return false;
    }
    __ID___stmHttpRequest.onreadystatechange = function() { __ID___stmGetSubcats( __ID___stmHttpRequest,deep,catid ); };
    __ID___stmHttpRequest.open( 'GET', url, true );
    __ID___stmHttpRequest.send( null );
}
function __ID___stmGetSubcats( __ID___stmHttpRequest, deep, catid )
{
	if ( __ID___stmHttpRequest.readyState == 4 ) {
		if ( __ID___stmHttpRequest.status == 200 ) {
			if( SP_id( "__ID___imgFolder" + catid )  == undefined ) {
				window.setTimeout( function() { __ID___stmGetSubcats( __ID___stmHttpRequest, deep, catid ); } , 200 );
			}
			else {
				SP_id( "__ID___imgFolder" + catid ).src = __ID___stmImgs[ 'folderOpen' ];
	        	 if ( __ID___stmcid == __ID___stmLastNode ) {
	        	 	SP_id( "__ID___imgExpand" + catid ).src = __ID___stmImgs[ 'minusBottom' ];
	        	 }
	        	 else {
	        		 if( SP_id( "__ID___imgExpand" + catid ).src == __ID___stmImgs[ 'plusBottom' ] ) {
	        			 SP_id( "__ID___imgExpand" + catid ).src = __ID___stmImgs[ 'minusBottom' ];
	        		 }
	        		 else {
	        			 SP_id( "__ID___imgExpand" + catid ).src = __ID___stmImgs[ 'minus' ];
	        		 }
	        	 }
	        	 SP_id( "__ID___imgUrlExpand" + catid ).href = "javascript:__ID___stmColapse( " + catid + ", " + deep + " );";
	        	 SP_id( "__ID___childsContainer" + catid ).style.display = "";
	        	 __ID___stmAddSubcats( __ID___stmHttpRequest.responseXML, deep, catid );
			}
        }
        else {
//            SobiPro.Alert( '__FAIL_MSG__' );
        }
    }
}
function __ID___stmColapse( id, deep )
{
	SP_id( "__ID___childsContainer" + id ).style.display = "none";
	SP_id( "__ID___imgFolder" + id ).src = __ID___stmImgs[ 'folder' ];
	if( id == __ID___stmLastNode ) {
		SP_id( "__ID___imgExpand" + id ).src = __ID___stmImgs[ 'plusBottom' ];
	}
   	else if(SP_id( "__ID___imgExpand" + __ID___stmcid ).src == __ID___stmImgs[ 'minusBottom' ] ){
	 	SP_id( "__ID___imgExpand" + __ID___stmcid ).src = __ID___stmImgs[ 'plusBottom' ];
	}
	else {
		SP_id( "__ID___imgExpand" + id ).src = __ID___stmImgs[ 'plus' ];
	}
	SP_id( "__ID___imgUrlExpand" + id ).href = "javascript:__ID___stmExpand( " + id + ", " + deep + ", " + __ID___stmPid + " );";
}

