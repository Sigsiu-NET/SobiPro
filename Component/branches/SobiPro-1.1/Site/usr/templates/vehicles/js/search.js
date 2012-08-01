/**
 * @version: $Id: search.js 1971 2011-11-07 14:45:04Z Radek Suski $
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
 * $Date: 2011-11-07 15:45:04 +0100 (Mon, 07 Nov 2011) $
 * $Revision: 1971 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/usr/templates/vehicles/js/search.js $
 */
try{ jQuery.noConflict(); } catch( e ) {}
jQuery( document ).ready(  function() {
	spSearchDefStr = jQuery( '#SPSearchBox' ).val();
	jQuery( '#SPSearchBox' ).bind( 'click', function() {
		if( jQuery( '#SPSearchBox' ).val() == spSearchDefStr ) {
			jQuery( '#SPSearchBox' ).val( '' );
		};
	} );
	jQuery( '#SPSearchBox' ).bind( 'blur', function() {
		if( jQuery( '#SPSearchBox' ).val() == '' ) {
			jQuery( '#SPSearchBox' ).val( spSearchDefStr );
		};
	} );
	try {
		jQuery( '#SPExtSearch' ).slideToggle( 'fast' );
		jQuery( '#SPExOptBt' ).bind( 'click', function() {
			jQuery( '#SPExtSearch' ).slideToggle( 'fast' );
		} );
	} catch( e ) {}
} );
