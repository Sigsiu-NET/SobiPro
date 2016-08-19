/**
 * @version: $Id$
 * @package: SobiPro Library

 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET

 * @copyright Copyright (C) 2006 - 2015 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license GNU/LGPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License version 3 as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/lgpl.html and https://www.sigsiu.net/licenses.

 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

 * $Date$
 * $Revision$
 * $Author$
 * $HeadURL$
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
						SobiPro.jQuery( '#SPVerUpd' ).html( output + '<div class="clearfix"></div>' );
					}
				}
			} );
		}
	} )
} );
