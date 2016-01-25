/**
 * @package: SobiRestara SobiPro Template
 *
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: https://www.Sigsiu.NET
 *
 * @copyright Copyright (C) 2006 - 2016 Sigsiu.NET GmbH (https://www.sigsiu.net). All rights reserved.
 * @license Released under Sigsiu.NET Template License V1.
 * You may use this SobiPro template on an unlimited number of SobiPro installations and may modify it for your needs.
 * You are not allowed to distribute modified or unmodified versions of this template for free or paid.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

// Autosuggest script for B3-Typeahead.js
SobiPro.jQuery( document ).ready( function ()
{
	SobiPro.jQuery( '.search-query' ).typeahead( {
		source: function ( query, typeahead )
		{
			var request = {
				'option': 'com_sobipro',
				'task': 'search.suggest',
				'sid': SobiProSection,
				'term': query,
				'format': 'raw'
			};
			var proxy = this;
			return SobiPro.jQuery.ajax( {
				'type': 'post',
				'url': 'index.php',
				'data': request,
				'dataType': 'json',
				success: function ( response )
				{
					responseData = [];
					if ( response.length ) {
						for ( var i = 0; i < response.length; i ++ ) {
							responseData[ i ] = { 'name': response[ i ] };
						}
						typeahead( responseData );
					}
				}
			} );
		},
		property: 'name'
	} );
} );
