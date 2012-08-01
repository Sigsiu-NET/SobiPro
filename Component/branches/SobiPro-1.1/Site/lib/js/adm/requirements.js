 /**
 * @version: $Id: requirements.js 1377 2011-05-19 16:19:16Z Sigrid Suski $
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
 * $Date: 2011-05-19 18:19:16 +0200 (Thu, 19 May 2011) $
 * $Revision: 1377 $
 * $Author: Sigrid Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/js/adm/requirements.js $
 */

var StatCount = 0;
var Retries = 0;
var SPWarn = 0;
var SPErr = 0;
var SPStart = 0;
var SPEnd = 0;

window.addEvent( 'domready', function() {
	e = SP_class( 'statInner' );
	$( 'StatSp' ).innerHTML = '<img src="' + SPLiveSite + 'media/sobipro/styles/spinner.gif"/>';
	for ( var i = 0, j = e.length; i < j; i++) {
		SP_Stats( e[ i ] );
	}	
});

function SP_ReqAgain()
{
	StatCount = 0;
	Retries = 0;
	SPWarn = 0;
	SPErr = 0;
	SPStart = 0;
	try { $( 'spReqBt' ).disabled = true; } catch( e ) {}
	$( 'spRReqBt' ).disabled = true;
	e = SP_class( 'statInner' );
	$( 'StatSp' ).innerHTML = '<img src="' + SPLiveSite + 'media/sobipro/styles/spinner.gif"/>';
	for ( var i = 0, j = e.length; i < j; i++) {
		SP_Stats( e[ i ] );
	}	
}

function SP_Stop()
{
	if( StatCount <= 0 ) {
		$( 'StatSp' ).innerHTML = '';
		$( 'StatMsg' ).innerHTML = SobiPro.Txt( 'Done' );
		try { $( 'spReqBt' ).disabled = false; } catch( e ) {}
		$( 'spRReqBt' ).disabled = false;
		$( 'spReqBtCont' ).style.display = '';
		$( 'spRDownBt' ).disabled = false;
		$( 'spRDownBt' ).style.display = '';
		var start = new Date().getTime();
		for ( var i = 0; i < 1e7; i++) {
			if ( ( new Date().getTime() - start ) > 1000 ) {
				break;
			}
		}		
		if( SPErr ) {
			SobiPro.Alert( 'REQUIREMENT_ERR' );
		}
		else if( SPWarn ) {
			SobiPro.Alert( 'REQUIREMENT_WARN' );
			SPStart = new Date().getTime();
		}
	}
	else {
		$( 'StatMsg' ).innerHTML = SobiPro.Txt( 'REQUIREMENT_WORKING_MSG' ).replace( '%d', StatCount );
	}
}

function SP_ReqEnd()
{
	if ( ( new Date().getTime() - SPStart ) < 4000 ) {
		alert( SobiPro.Txt( 'REQUIREMENT_READ_PLEASE' ).replace( '%d', Math.ceil( ( new Date().getTime() - SPStart ) / 1000 ) ) );
		SPStart = 0;
	}
	else {
		document.location = spHome;
	}
	return false;
}

function SP_Download()
{
	document.location = spDownl;
}

function SP_Stats( el ) 
{
	var start = new Date().getTime();
	for ( var i = 0; i < 1e7; i++) {
		if ( ( new Date().getTime() - start ) > 10 ) {
			break;
		}
	}
	var spinner = '<img src="' + SPLiveSite + 'media/sobipro/styles/spinner.gif"/>';
	StatCount++;
	el.innerHTML = spinner;
	advAJAX.get( {
		url : spReq + "." + el.id,
		timeout : 50000,
		onTimeout : function() {
			StatCount--;
			el.innerHTML = SobiPro.Txt( 'Connection timed out.' );
			SP_Stop();
		},
		retry : 5,
		retryDelay : 2000,
		onRetry : function() {
			el.innerHTML = spinner + SobiPro.Txt( 'Retry connection...' );
		},
		onRetryDelay : function() {
			el.innerHTML = SobiPro.Txt( 'Awaiting retry...' );
		},
		onError : function( obj ) {
			el.innerHTML = SobiPro.Txt( 'Error ... ' ) + obj.status;
			StatCount--;
			SP_Stop();
		},
		onSuccess : function( obj ) {
			if ( obj.responseText.length < 1000 ) {
				try {
					jobj = eval( '(' + obj.responseText + ')' );
					el.innerHTML = jobj.content;
					$( 'i' + el.id ).innerHTML = jobj.ico;
					if( jobj.error ) {
						SPErr++;
					}
					if( jobj.warning ) {
						SPWarn++;
					}
					StatCount--;
					SP_Stop();
				}
				catch( err ) {
					el.innerHTML = obj.responseText;
					StatCount--;
					SP_Stats( el );
				}
			} 
			else {
				Retries++;
				if ( Retries < 25 ) {
					el.innerHTML = SobiPro.Txt( 'Too long answer' );
					StatCount--;
					SP_Stats( el );
				} 
				else {
					el.innerHTML = SobiPro.Txt( 'Too long answer. Limit expired. Skipping' );
					StatCount--;
					SP_Stop();
				}
			}
		}
	} );
}