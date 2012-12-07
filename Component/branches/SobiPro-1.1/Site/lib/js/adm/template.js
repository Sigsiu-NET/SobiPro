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
} );

