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
window.addEvent( 'domready', function()
{
	$( 'sp-panel-updates' ).addEvent( 'click', function()
	{
		if( SPUpdSemaphor == 0 ) {
			$( 'SPVerUpd' ).innerHTML = SobiPro.Txt( 'CHECKING_FOR_UPDATES' ) + '&nbsp;<img src="../media/sobipro/styles/progress.gif"/>';
			SPUpdSemaphor = 1;
			new SobiPro.Json( SobiProAdmUrl.replace( '%task%', 'extensions.updates' ), {
				onComplete: function( updates )
				{
					if( updates.err ) {
						$( 'SPVerUpd' ).innerHTML = '<span style="color:red;font-weight:bold;">' + updates.err + '</span>';
					}
					else {
						var output = '';
						for ( var x in updates ) {
							name = updates[ x ].name + ' (' + updates[ x ].type + ') ';
							if( updates[ x ].update == 'false' ) {
								state = '&nbsp;<span style="color:#0000F7;">' + updates[ x ].update_txt + '</span>';
							}
							else {
								state = '&nbsp;<span style="color:#F7022B;font-weight: bold">' + updates[ x ].update_txt + '</span>';
							}
							output += '<div style="min-width:260px; float: left; padding: 1px; margin-left: 10px;">' + name + '</div><div style="padding: 1px;"> ' + state + '</div>';
						}
						$( 'SPVerUpd' ).innerHTML = output + '<div style="clear: both;"></div>';
						if( SPResize ) {
							SPAcc.display( 2 );
						}
						SPAcc.display( 1 );
					}
				}
			} ).send();
		}
	} );
} );
