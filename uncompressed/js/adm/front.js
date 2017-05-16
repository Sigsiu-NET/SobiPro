/**
 * @package: SobiPro Library
 *
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: httsp://www.Sigsiu.NET
 *
 * @copyright Copyright (C) 2006 - 2017 Sigsiu.NET GmbH (https://www.sigsiu.net). All rights reserved.
 * @license GNU/LGPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License version 3
 * as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/lgpl.html and https://www.sigsiu.net/licenses.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
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
						SobiPro.jQuery( '#SPVerUpd' ).html( '<span class="outdated">' + updates.err + '</span>' );
					}
					else {
						var output = '';
						for ( var x in updates ) {
							var name = updates[ x ].name;
							// var name = updates[ x ].name + ' (' + updates[ x ].type + ') ';
							var title = 'title="' + name + ': ' + updates[ x ].update_txt + '"';
							if ( updates[ x ].update == 'false' ) {
								var state = '<i class="icon-ok"' + title + '></i>';
								var addclass = ' uptodate';
								// var state = '&nbsp;<span class="uptodate">' + updates[ x ].update_txt + '</span>';
							}
							else {
								var state = '<i class="icon-remove-sign" ' + title + '></i>';
								var addclass = ' outdated';
								name = '<a href="index.php?option=com_sobipro&task=extensions.installed" ' + title + '>' + name + '</a>'
								// var state = '&nbsp;<span class="outdated">' + updates[ x ].update_txt + '</span>';
							}
							output += '<div class="spUpdatesApp">' + '<span class="spUpdatesState' +  addclass + '"> ' + state + ' ' + name + ' (' + updates[ x ].update_txt + ')</span></div>';
							// output += '<div class="spUpdatesApp">' + name + '</div><div class="spUpdatesState"> ' + state + '</div>';
						}
						SobiPro.jQuery( '#SPVerUpd' ).html( output + '<div class="clearfix"></div>' );
					}
				}
			} );
		}
	} )
} );
