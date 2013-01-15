/**
 * @version: $Id$
 * @package: SobiPro Library

 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET

 * @copyright Copyright (C) 2006 - 2013 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license GNU/LGPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License version 3 as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/lgpl.html and http://sobipro.sigsiu.net/licenses.

 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

 * $Date$
 * $Revision$
 * $Author$
 * $HeadURL$
 */

function SPinitMenu( el )
 {
 	SobiPro.onReady( function () { SPcloseMenu(); SP_id( el ).style.display = 'block'; } );
 }

 function SPopenMenu( el )
 {
	SPcloseMenu();
 	SP_id( el ).style.display = 'block';
 }

 function SPcloseMenu()
 {
 	var tabs = SP_class( 'SPcontentTabHeader', SP_id( 'SPaccordionTabs' ) );
	for( var i = 0, j = tabs.length; i < j; i++ ) {
		tabs[ i ].style.display = 'none';
	}
 }
