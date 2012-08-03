/**
 * @version: $Id: sobipro.js 1243 2011-04-20 19:08:19Z Radek Suski $
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
 * $Date: 2011-04-20 21:08:19 +0200 (Wed, 20 Apr 2011) $
 * $Revision: 1243 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/js/sobipro.js $
 */

function SobiPro()
{
	this.Json = function ( url, options )
	{
		try {
			return new Json.Remote( url, options );
		}
		catch ( e ) {
			options.url = url;
			return new Request.JSON( options );
		}
	};

	this.Request = function ( url, options )
	{
		try {
			return new Ajax( url, options );
		}
		catch ( e ) {
			options.url = url;
			var r = new Request( options );
			r.request = function ()
			{
				this.send();
			};
			return r;
		}
	};

	this.setLang = function ( obj )
	{
		this.lang = obj;
	};

	this.setJq = function ( j )
	{
		this.jQuery = j;
	}

	this.onReady = function ( f )
	{
		this.functions.include( f );
	};

	this.Ready = function ()
	{
		for ( var i = 0, j = this.functions.length; i < j; i++ ) {
			f = this.functions[ i ];
			f();
		}
	};

	this.Txt = function ( text )
	{
		string = text.toUpperCase();
		string = string.replace( / /g, '_' );
		string = string.replace( /[^A-Z0-9_]/g, '' );
		if ( this.lang != null && this.lang[ string ] ) {
			return this.lang[ string ].replace( '{newline}', "\n" );
		}
		else {
			return text;
		}
	};

	this.StripSlashes = function ( txt )
	{
		txt = txt.replace( /\\'/g, '\'' );
		txt = txt.replace( /\\"/g, '"' );
		txt = txt.replace( /\\0/g, '\0' );
		txt = txt.replace( /\\\\/g, '\\' );
		return txt;
	};

	this.htmlEntities = function ( txt )
	{
		var e = document.createElement( 'pre' );
		txt = txt.replace( /<\/?[^>]+>/gi, '' );
		e.innerHTML = txt;
		try {
			r = e.firstChild.nodeValue.replace( /^\s*/, '' ).replace( /\s*$/, '' );
		}
		catch ( e ) {
			try {
				r = e.nodeValue.replace( /^\s*/, '' ).replace( /\s*$/, '' );
			}
			catch ( e ) {
				r = txt;
			}
		}
		return r;
	};

	this.Alert = function ( text )
	{
		alert( this.Txt( text ) );
	};

	this.functions = new Array();
	this.jQuery = null;
	this.lang = null;

}

var SobiPro = new SobiPro();

function SPinclude( path )
{
	var el = window.document.createElement( 'script' );
	el.setAttribute( 'src', path );
	el.setAttribute( 'type', 'text/javascript' );
	window.document.head.appendChild( el );
}

function SP_node( node )
{
	if ( !node ) {
		node = document;
	}
	return node;
}

function SP_id( id, node )
{
	return SP_node( node ).getElementById( id );
}

function SP_name( name, node )
{
	return SP_node( node ).getElementsByName( name );
}

function SP_tag( name, node )
{
	return SP_node( node ).getElementsByTagName( name );
}

function SP_class( name, node )
{
	var elements = [];
	var filter = new RegExp( '\\b' + name + '\\b' );
	var e = SP_node( node ).getElementsByTagName( "*" );
	for ( var i = 0, j = e.length; i < j; i++ ) {
		if ( filter.test( e[ i ].className ) ) {
			elements.push( e[ i ] );
		}
	}
	return elements;
}

function SPForm()
{
	this.values = new Array();
	this.request = function ()
	{
		string = '';
		for ( i = 0; i < this.values.length; i++ ) {
			string = string + this.values[ i ][ 0 ] + '=' + encodeURI( this.values[ i ][ 1 ] ) + '&';
		}
		return string;
	};
	this.parse = function ( el )
	{
		for ( var i = 0; i < el.childNodes.length; i++ ) {
			tagName = new String( el.childNodes[ i ].tagName ).toLowerCase();
			var e = el.childNodes[ i ];
			if ( tagName == 'input' ) {
				tagName = e.type;
			}
			switch ( tagName ) {
				case 'text':
				case 'textarea':
					this.values.push( new Array( e.name, e.value ) );
					break;
				case 'radio':
				case 'checkbox':
					if ( e.checked == true ) {
						this.values.push( new Array( e.name, e.value ) );
					}
					break;
				case 'select':
					var opt = e.options;
					var selected = false;
					for ( var j = 0; j < opt.length; j++ ) {
						if ( ( opt[ j ].value != 0 && opt[ j ].value != '' ) && opt[ j ].selected == true ) {
							this.values.push( new Array( e.name, opt[ j ].value ) );
							break;
						}
					}
					break;
				default:
					if ( el.childNodes[ i ].childNodes.length > 0 ) {
						r = this.parse( e );
					}
					break;
			}
		}
		return this;
	};
}

function SPValidator()
{
	this.radio = [];
	this.labels = [];
	this.background = 'red';
	this.escape = function ( el, index )
	{
		if ( !index ) {
			index = el.id;
		}
		index = index.replace( /[^\a-z0-9\-\_\.]/g, '' );
		this.highlight( SP_id( index ) );
		alert( SobiPro.Txt( 'ADD_ENTRY_FIELD_REQUIRED' ).replace( '$field', '"' + this.label( index ) + '"' ) );
		return false;
	};
	this.highlight = function ( el )
	{
		currStyle = el.style.backgroundColor;
		if ( el.attachEvent ) {
			el.attachEvent( 'onclick', function ()
			{
				this.style.backgroundColor = currStyle;
			} );
		}
		else {
			el.addEventListener( 'click', function ()
			{
				this.style.backgroundColor = currStyle;
			}, false );
		}
		el.style.backgroundColor = this.background;
	};
	this.label = function ( field )
	{
		var ElName = SobiPro.Txt( 'RED_HIGHLIGHTED_FIELD' );
		for ( var j = 0; j < this.labels.length; j++ ) {
			if ( this.labels[ j ].getAttribute( 'for' ) == field ) {
				temp = SobiPro.htmlEntities( this.labels[ j ].innerHTML ).replace( /\s\s/g, '' );
				if ( temp != '' ) {
					ElName = temp;
				}
				break;
			}
		}
		return ElName;
	};
	this.filter = function ( e )
	{
		r = true;
		try {
			for ( var f = 0; f <= SPFilter.length; f++ ) {
				if ( e.name == SPFilter[ f ].name ) {
					val = SP_id( SPFilter[ f ].name ).value;
					var filter = eval( "new RegExp(" + SPFilter[ f ].filter + ")" );
					if ( val != '' && ( filter.test( val ) == false ) ) {
						this.highlight( e );
						alert( SPFilter[ f ].msg.replace( '$field', '"' + this.label( e.id ) + '"' ) );
						r = false;
						break;
					}
				}
			}
		}
		catch ( ex ) {
		}
		return r;
	};
	this.validate = function ( el )
	{
		this.labels = SP_tag( 'label' );
		var r = true;
		for ( var i = 0; i < el.childNodes.length; i++ ) {
			tagName = new String( el.childNodes[ i ].tagName );
			tagName = tagName.toLowerCase();
			var e = el.childNodes[ i ];
			if ( tagName == 'input' ) {
				tagName = e.type;
			}
			if ( tagName == 'text' || tagName == 'textarea' ) {
				r = this.filter( e );
			}
			switch ( tagName ) {
				case 'text':
					if ( e.className.indexOf( 'required' ) != -1 ) {
						if ( e.value == '' ) {
							r = this.escape( e );
							break;
						}
					}
					break;
				case 'radio':
				case 'checkbox':
					if ( e.className.indexOf( 'required' ) != -1 ) {
						if ( !( this.radio.some( function ( a )
						{
							return a == e.name;
						} ) ) ) {
							r = false;
							index = SP_name( e.name ).length;
							re = SP_name( e.name );
							for ( var i = 0; i < index; i++ ) {
								if ( re[ i ].checked == true ) {
									r = true;
									break;
								}
							}
							if ( r == true ) {
								this.radio.push( e.name );
							}
							else {
								this.escape( e, e.name );
							}
						}
					}
					break;
				case 'textarea':
					if ( e.className.indexOf( 'required' ) != -1 ) {
						if ( e.value == '' ) {
							r = this.escape( e );
							break;
						}
					}
					break;
				case 'select':
					if ( e.className.indexOf( 'required' ) != -1 ) {
						var opt = e.options;
						var selected = false;
						for ( var j = 0; j < opt.length; j++ ) {
							if ( ( opt[ j ].value != 0 && opt[ j ].value != '' ) && opt[ j ].selected == true ) {
								selected = true;
								break;
							}
						}
						if ( selected == false ) {
							r = this.escape( e );
							break;
						}
					}
					break;
				default:
					if ( el.childNodes[ i ].childNodes.length > 0 ) {
						r = this.validate( e );
					}
					break;
			}
			if ( r == false ) {
				break;
			}
		}
		return r;
	};
}
function SPValidateForm()
{
	return new SPValidator().validate( SP_id( 'SPAdminForm' ) );
}

function SPValidate( form )
{
	return new SPValidator().validate( form );
}

function SP_parentPath( sid )
{

}

function SPcancelEdit()
{
	SP_id( 'SP_task' ).value = 'entry.cancel';
	SP_id( 'spEntryForm' ).submit();
}
