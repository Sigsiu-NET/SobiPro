/**
 * Created with JetBrains PhpStorm.
 * User: neo
 * Date: 14/8/2012
 * Time: 17:34
 * To change this template use File | Settings | File Templates.
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
	}
	if ( mode ) {
		options[ 'mode' ] = mode;
	}
	var editor = CodeMirror.fromTextArea( document.getElementById( 'file_content' ), options );
	editor.setSize( '95%', '1000px' );
	SobiPro.jQuery( '#SPAdminForm' ).bind( 'BeforeAjaxSubmit', function ()
	{
		editor.save();
	} );
}
