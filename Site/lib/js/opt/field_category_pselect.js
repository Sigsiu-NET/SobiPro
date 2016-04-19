/**
 * @version: $Id: search.js 4387 2015-02-19 12:24:35Z Radek Suski $
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
 * File location: components/com_sobipro/usr/templates/default2/js/search.js $
 */

SobiPro.jQuery( document ).ready( function ()
{

	SobiPro.jQuery.fn.SPCatInputSelector = function ()
	{
		var Proxy = this;
		this.Categories = [];
		this.Parents = [];
		this.Select = this;
		this.Relations = [];
		this.Name = this.attr( 'name' );

		this.hide();
		this.attr( 'name', '' );

		if ( ! ( this.Categories.length ) ) {
			SobiPro.jQuery.ajax( {
				'type': 'post',
				'url': SPLiveSite + SobiProUrl.replace( '%task%', this.data( 'task' ) ),
				'dataType': 'json',
				'data': {
					'sid': SobiProSection,
					'format': 'raw',
					'tmpl': 'component'
				}
			} ).done( function ( data )
			{
				SobiPro.jQuery.fn.SPCatInputSelectorCategories = data.categories;
				Proxy.CreateMap( data.categories, SobiProSection );
				Proxy.SubSelect( SobiPro.jQuery.fn.SPCatInputSelectorCategories );
				if ( Proxy.data( 'selected' ) > 0 ) {
					var Path = [];
					var Current = Proxy.data( 'selected' );
					while ( Current != SobiProSection ) {
						Path.push( parseInt( Current ) );
						if ( Current ) {
							Current = Proxy.Parents[ Current ];
						}
						else {
							Current = - 1;
							break;
						}
					}
					Path.reverse();
					SobiPro.jQuery.each( Path, function ( i, c )
					{
						Proxy.Select.val( c );
						Proxy.Select.trigger( 'change' );
					} );
				}
			} );
		}
		this.CreateMap = function ( categories, parent )
		{
			SobiPro.jQuery.each( categories, function ( i, e )
			{
				Proxy.Parents[ e.sid ] = parent;
				Proxy.Relations[ e.sid ] = e;
				Proxy.CreateMap( e.childs, e.sid );
			} )
		};

		this.SubSelect = function ( options )
		{
			if ( Object.keys( options ).length ) {
				var Select = Proxy.Select.clone();
				Select.attr( 'name', '' )
					.find( 'option' )
					.remove()
					.end();
				SobiPro.jQuery( '<option/>', { value: 0, text: '-----' } )
					.appendTo( Select );
				SobiPro.jQuery.each( options, function ( i, e )
				{
					SobiPro.jQuery( '<option/>', { value: e.sid, text: e.name } )
						.appendTo( Select );
				} );
				Select.insertAfter( this.Select );
				Select.show();
				this.Select = Select;
				this.Select.change(
					function ()
					{
						var I = SobiPro.jQuery( this );
						if ( I.nextAll( 'select' ).length ) {
							I.nextAll( 'select' ).remove();
							Proxy.Select = I;
						}
						if ( I.val() != 0 ) {
							if ( I.prevAll( 'select' ).length ) {
								I.prevAll( 'select' ).attr( 'name', '' );
							}
							I.attr( 'name', Proxy.Name );
						}
						else if ( I.val() == 0 ) {
							I.attr( 'name', '' );
							I.prev( 'select' ).attr( 'name', Proxy.Name );
						}
						Proxy.SubSelect( Proxy.Relations[ I.val() ].childs );
					}
				);
			}
		}
	};
	SobiPro.jQuery( '.ctrl-field-category' ).SPCatInputSelector();
} );
