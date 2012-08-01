 /**
 * @version: $Id: menu.js 551 2011-01-11 14:34:26Z Radek Suski $
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
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/js/menu.js $
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
