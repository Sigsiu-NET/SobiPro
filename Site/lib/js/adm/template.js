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

SobiPro.jQuery( document ).ready( function ()
{
	SobiPro.jQuery( '[rel^="template.clone"]' ).unbind( 'click' ).click( function ( e )
	{
		SobiPro.jQuery( '#SP_templateNewName' ).val( window.prompt( SobiPro.Txt( 'CLONE_TEMPL' ) ) );
		if ( SobiPro.jQuery( '#SP_templateNewName' ).val() ) {
			SobiPro.jQuery( '#SP_task' ).val( SobiPro.jQuery( this ).attr( 'rel' ) );
			SobiPro.jQuery( '#SPAdminForm' ).submit();
		}
	} );
	SobiPro.jQuery( '[rel^="template.saveAs"]' ).unbind( 'click' ).click( function ( e )
	{
		var name = window.prompt( SobiPro.Txt( 'SAVE_AS_TEMPL_FILE' ), SobiPro.jQuery( '#SP_filePath' ).val() );
		if ( name ) {
			SobiPro.jQuery( '#SP_fileName' ).val( name.replace( /\//g, "." ).replace( /\\/g, "." ) );
			SobiPro.jQuery( '#SP_task' ).val( SobiPro.jQuery( this ).attr( 'rel' ) );
			SobiPro.jQuery( '#SP_method' ).val( 'html' );
			SobiPro.jQuery( '#SPAdminForm' ).submit();
		}
	} );
} );

function SPInitTplEditor( mode )
{
	var options = {
		lineNumbers:true,
		matchBrackets:true,
		indentUnit:4,
		indentWithTabs:true,
		enterMode:"keep",
		tabMode:"shift"
	};
	if ( mode ) {
		options[ 'mode' ] = mode;
	}
	var editor = CodeMirror.fromTextArea( document.getElementById( 'file_content' ), options );
	editor.setSize( '100%', '1000px' );
	SobiPro.jQuery( '#SPAdminForm' ).bind( 'BeforeAjaxSubmit', function ()
	{
		editor.save();
	} );
}
