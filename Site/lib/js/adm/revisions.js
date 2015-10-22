/**
* @package: SobiPro Library

* @author
* Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
* Email: sobi[at]sigsiu.net
* Url: https://www.Sigsiu.NET

	* @copyright Copyright (C) 2006 - 2015 Sigsiu.NET GmbH (https://www.sigsiu.net). All rights reserved.
* @license GNU/LGPL Version 3
* This program is free software: you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License version 3
* as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
* See http://www.gnu.org/licenses/lgpl.html and https://www.sigsiu.net/licenses.
	*
* This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 */
/**
 * To change this template use File | Settings | File Templates.
 */

SobiPro.jQuery( document ).ready( function ()
{
	SobiPro.jQuery( '.ctrl-revision-compare' ).click( function ( e )
	{
		e.preventDefault();
		SobiPro.jQuery( '#revisions-window' ).modal();
		SobiPro.jQuery( '.ctrl-diff' ).html( '<i class="icon-spinner icon-spin icon-large"></i>' );
		var request = {
			'option': 'com_sobipro',
			'task': 'entry.revisions',
			'sid': SobiPro.jQuery( '#SP_sid' ).val(),
			'format': 'raw',
			'tmpl': 'component',
			'method': 'xhr',
			'revision': SobiPro.jQuery( '#SP_revision' ).val(),
			'fid': SobiPro.jQuery( this ).data( 'fid' ),
			'html': 1
		};
		SobiPro.jQuery.ajax( {
			url: 'index.php',
			type: 'post',
			dataType: 'json',
			data: request
		} ).done( function ( response )
			{
				SobiPro.jQuery( '.ctrl-diff' ).html( response.diff );
			} );
	} );
} );

