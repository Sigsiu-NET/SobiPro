 /**
 * @version: $Id: init.js 551 2011-01-11 14:34:26Z Radek Suski $
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
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/js/calendar/init.js $
 */
 // Created at __CREATED__ by Sobi Pro Component
function SPCalendar( id, bid, format )
{
	if( !format ) {
		format = "__FORMAT__";
	}
	var el = SP_id( id );
	if ( calendar != null ) {
		calendar.hide();
		calendar.parseDate( el.value );
	} else {
		var cal = new Calendar( true, null, SPSelectedDate, SPCloseCal );
		calendar = cal;
		SobiCal = cal;
		cal.setRange( 1800, 2300 );
		calendar.create();
	}
	calendar.setDateFormat( format );
	calendar.setTtDateFormat( "__FORMAT_TXT__" )
	calendar.parseDate( el.value );
	calendar.sel = el;
	calendar.showAtElement( SP_id( id ) );
	return false;
}
function SPCloseCal( c ) { c.hide(); }
function SPSelectedDate( c, d ) { c.sel.value = d; }