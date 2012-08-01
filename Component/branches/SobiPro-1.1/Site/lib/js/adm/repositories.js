 /**
 * @version: $Id: repositories.js 967 2011-03-09 15:54:50Z Radek Suski $
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
 * $Date: 2011-03-09 16:54:50 +0100 (Wed, 09 Mar 2011) $
 * $Revision: 967 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/js/adm/repositories.js $
 */
var spresponse = null;
var sprepo = null;
var spcallback = null;
window.addEvent( 'domready', function() {
	spresponse = new Fx.Slide( 'spresponse' );
	spresponse.hide(); 
	$( 'spaddrepo' ).addEvent( 'click', function( e ) {
		e = new Event( e );
		e.stop();
		sprepo = $( 'sprepo' ).value;
		if( sprepo == '' ) {
			SobiPro.Alert( 'NO_REPO' );
		}
		else {
			$( 'spaddrepo' ).disabled = true;
			$( 'sprepo' ).disabled = true;
			$( 'spresponse' ).innerHTML = '';
			new SobiPro.Request( 
					spReq.replace( '%task%', 'addRepo' ) + '&repo=' + sprepo, 
					{ 
						method: 'get', 
						onRequest: function() { 
							SP_RepoWait( true ); 
						},
						onComplete: function( responseText ) {
							spresponse.slideIn();
							$( 'spresponse' ).innerHTML = responseText; 
							SP_RepoWait( false );
						}
					} 
			).request();		
		}
	} );
} );

function SP_RepoWait( on )
{
	if( on ) {
		$( 'sprwait' ).innerHTML = '<img src="/media/sobipro/styles/progress.gif"/>';
	}
	else {
		$( 'sprwait' ).innerHTML = '&nbsp;';
	}
}

function SP_CertConf()
{	
	var request = new SobiPro.Json(
		spReq.replace( '%task%', 'confirmRepo' ) + '&repo=' + sprepo,
		{
			onRequest: function() { 
				SP_RepoWait( true ); 
			},
			onComplete: function( jsonObj, jsons )
			{
				SP_RepoWait( false );
				if( jsonObj.msg.search( /DOCTYPE html PUBLIC/ ) != -1 ) {
					SobiPro.Alert( 'Session expired' );
					document.location = document.location;
				}
				if ( jsonObj.callback == undefined ) {
					spresponse.slideOut();
					$( 'spresponse' ).innerHTML = '';
					$( 'sprepo' ).disabled = false;
					$( 'spaddrepo' ).disabled = false;
					$( 'sprepo' ).value = '';
					alert( jsonObj.msg.replace(/<br\/>/gi, '\n') );
					document.location = document.location;
				}
				else {
					spcallback = jsonObj.callback;
					sprepo = jsonObj.repo;
					$( 'spresponse' ).innerHTML = jsonObj.msg;
				}
			}
		}
	).send();
}

function SP_RepoCallback()
{
	var requestr = new SPForm().parse( $( 'SPAdminForm' ) ).request();
	r = spReq.replace( '%task%', 'registerRepo' ) + '&repo=' + sprepo + '&' + requestr + 'callback=' + spcallback;
	var request = new SobiPro.Json( r, {
		onRequest: function() { 
			SP_RepoWait( true ); 
		},
		onComplete: function( jsonObj, jsons )
		{
			SP_RepoWait( false );
			if( jsonObj.msg.search( /DOCTYPE html PUBLIC/ ) != -1 ) {
				SobiPro.Alert( 'Session expired' );
				document.location = document.location;
			}			
			if ( jsonObj.callback == undefined ) {
				spresponse.slideOut();
				$( 'spresponse' ).innerHTML = '';
				$( 'sprepo' ).disabled = false;
				$( 'spaddrepo' ).disabled = false;
				$( 'sprepo' ).value = '';
				alert( jsonObj.msg.replace(/<br\/>/gi, '\n') );
				document.location = document.location;
			}
			else {
				spcallback = jsonObj.callback;
				$( 'spresponse' ).innerHTML = jsonObj.msg;
			}
		}
	} ).send();
}

function SP_CertNotConf()
{	
	SobiPro.Request( spReq.replace( '%task%', 'delRepo' ) + '&repo=' + sprepo, { method: 'get' } ).request();
	spresponse.slideOut();
	$( 'sprepo' ).disabled = false;
	$( 'spaddrepo' ).disabled = false;
}