/**
 * @version: $Id: front.js 2078 2011-12-16 16:11:14Z Radek Suski $
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
 * $Date: 2011-12-16 17:11:14 +0100 (Fri, 16 Dec 2011) $
 * $Revision: 2078 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/js/adm/front.js $
 */
var SPUpdSemaphor = 0;
SobiPro.jQuery( document ).ready( function ()
{
	SobiPro.jQuery( '#SobiProUpdates' ).on( 'shown', function ()
	{
		if ( SPUpdSemaphor == 0 ) {
			SPUpdSemaphor = 1;
			SobiPro.jQuery( '#SPVerUpd' ).html( SobiPro.Txt( 'CHECKING_FOR_UPDATES' ) + '&nbsp;<img src="../media/sobipro/adm/progress.gif"/>' );
			SobiPro.jQuery.ajax( {
				url:SobiProAdmUrl.replace( '%task%', 'extensions.updates' ),
				dataType:'json',
				success:function ( updates )
				{
					"use strict";
					if ( updates.err ) {
						SobiPro.jQuery( '#SPVerUpd' ).html( '<span style="color:#C60000; font-weight:bold;">' + updates.err + '</span>' );
					}
					else {
						var output = '';
						for ( var x in updates ) {
							var name = updates[ x ].name + ' (' + updates[ x ].type + ') ';
							if ( updates[ x ].update == 'false' ) {
								var state = '&nbsp;<span style="color:#008000;">' + updates[ x ].update_txt + '</span>';
							}
							else {
								var state = '&nbsp;<span style="color:#C60000; font-weight: bold">' + updates[ x ].update_txt + '</span>';
							}
							output += '<div class="spUpdatesApp">' + name + '</div><div class="spUpdatesState"> ' + state + '</div>';
						}
						SobiPro.jQuery( '#SPVerUpd' ).html( output + '<div class="clearall"></div>' );
					}
				}
			} );
		}
	} )
} );
