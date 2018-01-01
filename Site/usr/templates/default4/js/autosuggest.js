/**
 * @package: SobiPro Component for Joomla!
 *
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: https://www.Sigsiu.NET
 *
 * @copyright Copyright (C) 2006 - 2017 Sigsiu.NET GmbH (https://www.sigsiu.net). All rights reserved.
 * @license GNU/GPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3
 * as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/gpl.html and https://www.sigsiu.net/licenses.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 */

// Autosuggest script for B3-Typeahead.js
SobiPro.jQuery( document ).ready( function () {
	SobiPro.jQuery( '.search-query' ).typeahead( {
		source: function ( query, typeahead ) {
			const request = {
				'option': 'com_sobipro',
				'task': 'search.suggest',
				'sid': SobiProSection,
				'term': query,
				'format': 'raw'
			};
			// const proxy = this;
			return SobiPro.jQuery.ajax( {
				'type': 'post',
				'url': 'index.php',
				'data': request,
				'dataType': 'json',
				success: function ( response ) {
					let responseData = [];
					if ( response.length ) {
						for ( let i = 0; i < response.length; i++ ) {
							responseData[ i ] = {'name': response[ i ]};
						}
						typeahead( responseData );
					}
				}
			} );
		},
		property: 'name',
		sorter: function ( items ) {
			return items;
		},
		matcher: function ( item ) {
			return true;
		}
	} );
} );
