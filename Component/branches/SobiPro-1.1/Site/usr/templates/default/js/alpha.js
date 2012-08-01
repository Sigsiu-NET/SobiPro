/**
 * @version: $Id: alpha.js 1503 2011-06-21 15:31:41Z Radek Suski $
 * @package: SobiPro Template
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
 * $Date: 2011-06-21 17:31:41 +0200 (Tue, 21 Jun 2011) $
 * $Revision: 1503 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/usr/templates/default/js/alpha.js $
 */
try{ jQuery.noConflict(); } catch( e ) {}
function SPAlphaSwitch( cid )
{
	jQuery( document ).ready( function() {
		sid = '#' + cid + 'Switch';
		jQuery( sid ).bind( 'change', function() {
			jQuery( sid ).disabled = true;
			jQuery( '#' + cid + 'Progress' ).html( '<img src="' + SPLiveSite + '/media/sobipro/styles/progress.gif" style="margin: 5px;" alt="loading"/>' );
			jQuery.ajax( { 
				url: SobiProUrl.replace( '%task%', 'list.alpha.switch.'+ jQuery( this ).val() ),
				data: { sid: SobiProSection, tmpl: "component", format: "raw" },
		          success: function( jsonObj ) {
		        	  jQuery( sid ).disabled = false;
		        	  jQuery( '#' + cid + 'Progress' ).html( '' );
		        	  jQuery( '#' + cid ).html( jsonObj.index );
		          }				
			} );
		} );
	} );
}