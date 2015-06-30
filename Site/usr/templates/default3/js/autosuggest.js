/**
 * @version: $Id: autosuggest.js 4387 2015-02-19 12:24:35Z Radek Suski $
 * @package: SobiPro Component for Joomla!

 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET

 * @copyright Copyright (C) 2006 - 2015 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license GNU/GPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3
 * as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/gpl.html and http://sobipro.sigsiu.net/licenses.

 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

 * $Date: 2015-02-19 13:24:35 +0100 (Thu, 19 Feb 2015) $
 * $Revision: 4387 $
 * $Author: Radek Suski $
 * File location: components/com_sobipro/usr/templates/default2/js/autosuggest.js $
 */

SobiPro.jQuery( document ).ready( function ()
{
	SobiPro.jQuery( '.search-query' ).typeahead( {
		source:function ( typeahead, query )
		{
			var request = { 'option':'com_sobipro', 'task':'search.suggest', 'sid':SobiProSection, 'term':query, 'format':'raw' };
			var proxy = this;
			return SobiPro.jQuery.ajax( {
				'type':'post',
				'url':'index.php',
				'data':request,
				'dataType':'json',
				success:function ( response )
				{
					responseData = [];
					if ( response.length ) {
						for ( var i = 0; i < response.length; i++ ) {
							responseData[ i ] = { 'name':response[ i ] };
						}
						typeahead.process( responseData );
						SobiPro.jQuery( proxy.$element ).after( SobiPro.jQuery( proxy.$menu ) );
					}
				}
			} );
		},
//		onselect:function ( obj )
//		{
//			SobiPro.DebOut( this )
//			this.$element.value( obj.name );
//			SobiPro.DebOut(SobiPro.jQuery( this.$element ).find( 'form' ))
//		},
		property:"name"
	} );
} );
