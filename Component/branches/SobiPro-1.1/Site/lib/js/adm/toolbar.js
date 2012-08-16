/**
 * Created with JetBrains PhpStorm.
 * User: neo
 * Date: 14/8/2012
 * Time: 17:34
 * To change this template use File | Settings | File Templates.
 */

SobiPro.jQuery( document ).ready( function ()
{
	SobiPro.jQuery( '#SPAdmToolbar a' ).click( function ( e )
	{
		var task = SobiPro.jQuery( this ).attr( 'rel' );
		if ( task.length ) {
			e.preventDefault();
			alert( '@todo: '+task );
			SobiPro.jQuery( '#SP_task' ).val( task );
			SobiPro.jQuery( '#SPAdminForm' ).submit();
		}
	} );
} );
