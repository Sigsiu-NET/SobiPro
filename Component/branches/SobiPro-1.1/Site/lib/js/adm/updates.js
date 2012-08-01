 /**
 * @version: $Id: updates.js 551 2011-01-11 14:34:26Z Radek Suski $
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
 * $Date: 2011-01-11 15:34:26 +0100 (Tue, 11 Jan 2011) $
 * $Revision: 551 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/js/adm/updates.js $
 */
var SPHandler = null;
var SPResponse = null;
var SPCallback = null;
var SPExt = null;

var SPSemaphor = 0;
window.addEvent( 'domready', function() {
	  SPResponse = new Fx.Slide( 'spupdating' );
	  SPResponse.hide(); 
	  SPDResponse = new Fx.Slide( 'spdresponse' );
	  SPDResponse.hide(); 
	  var spList = $$( "#toolbar-list a" )[ 0 ];
	  var spDownload = $$( "#toolbar-download a" )[ 0 ];
	  spList.onclick = null;
	  spDownload.onclick = null;
	  spList.addEvent( "click", function() {
		  if( SPSemaphor == 1 ) {
			  SobiPro.Alert( 'Operation blocked. Please wait until the pending operation is finished' );
			  return false;
		  }
		  SPSemaphor = 1;
		  $( 'splist' ).style.display = 'none';
		  $( 'spresponse' ).innerHTML = '';
		  $( 'spupd' ).style.display = 'block';
		  SP_SetCookie();
		  SPResponse.slideIn();
		  SP_RepoWait( true );
	  	  spDownload.onclick = null;
	  	  spList.onclick = null;
	  	  SobiPro.Request( SobiProAdmUrl.replace( '%task%', 'extensions.fetch' ), { method: 'get' } ).request();
		  SP_getMsg();
		  SPHandler = SP_getMsg.periodical( 1000 );
	  } );
	  spDownload.addEvent( "click", function() {
		  if( SPSemaphor == 1 ) {
			  SobiPro.Alert( 'Operation blocked. Please wait until the pending operation is finished' );
			  return false;
		  }
		  SPSemaphor = 1;
		  $( 'splist' ).style.display = 'none';
		  $( 'spresponse' ).innerHTML = '';
		  $( 'spdwn' ).style.display = 'block';
		  SP_SetCookie();
		  SPResponse.slideIn();
		  var requestr = new SPForm().parse( $( 'SPAdminForm' ) ).request();
		  new SobiPro.Json( SobiProAdmUrl.replace( '%task%', 'extensions.download' ) + '&' + requestr, {
				onRequest: function() { 
					SP_RepoWait( true ); 
				},
				onComplete: function( jsonObj, jsons )
				{
					SP_RepoWait( false );
					if ( jsonObj.callback == undefined ) {
						spresponse.slideOut();
						$( 'spresponse' ).innerHTML = '';
						alert( jsonObj.msg.replace(/<br\/>/gi, '\n') );
						document.location = document.location;
					}
					else {
						SPCallback = jsonObj.callback;
						SPExt = jsonObj.extension;
						SPDResponse.slideIn();
						$( 'spdresponse' ).innerHTML = jsonObj.msg;
					}
				}
		  } ).send();		  		 
		  SP_getMsg();
		  SPHandler = SP_getMsg.periodical( 1000 );
	  } );	  
} );

function SP_RepoCallback()
{
	var requestr = new SPForm().parse( $( 'SPAdminForm' ) ).request();
	r = SobiProAdmUrl.replace( '%task%', 'extensions.download' ) + '&plid=' + SPExt + '&callback=' + SPCallback;
	var request = new SobiPro.Json( r, {
		onRequest: function() { 
			SP_RepoWait( true ); 
		},
		onComplete: function( jsonObj, jsons )
		{
			SP_RepoWait( false );
			if ( jsonObj.callback == undefined ) {
				spresponse.slideOut();
				$( 'spresponse' ).innerHTML = '';
				alert( jsonObj.msg.replace(/<br\/>/gi, '\n') );
				document.location = document.location;
			}
			else {
				SPCallback = jsonObj.callback;
				$( 'spresponse' ).innerHTML = jsonObj.msg;
			}
		}
	} ).send();
}

function SP_RepoWait( on )
{
	if( on ) {
		$( 'sprwait' ).innerHTML = '<img src="../media/sobipro/styles/progress.gif"/>';
	}
	else {
		$( 'sprwait' ).innerHTML = '&nbsp;';
	}
}

function SP_SetCookie()
{
	  var exdate = new Date();
	  exdate.setHours( exdate.getHours() + 1 );
	  cid = exdate.getTime() + Math.floor( Math.random() * 11 ) * 100;
	  document.cookie = "SPro_sppbid=" + cid + ";expires=" + exdate.toUTCString() + ";path=/";	
}

var SP_getMsg = function() 
{
	new SobiPro.Json( 
			SobiProAdmUrl.replace( '%task%', 'progress' ), 
			{ 
				onComplete: function( jsonObj, jsons ) 
				{ 
					$( 'spresponse' ).innerHTML = jsonObj.msg; 
					if( jsonObj.interval != undefined ) {
						$clear( SPHandler );
						if( jsonObj.interval != 0 ) {
							SPHandler = SP_getMsg.periodical( jsonObj.interval );
						}
						else {
							SP_RepoWait( false );
						}
					}
					if( jsonObj.progress >= 99 ) {
						SP_RepoWait( false );
						window.location.reload();
						$clear( SPHandler ); 
					}
				} 
			} 
	).send();
};
