/**
 * Created with JetBrains PhpStorm.
 * User: neo
 * Date: 10/8/2012
 * Time: 18:36
 * To change this template use File | Settings | File Templates.
 */
SobiPro.jQuery( document ).ready( function ()
{
	SobiPro.jQuery( '.nav-tabs li a' ).click( function ( e )
	{
		e.preventDefault();
		SobiPro.jQuery( this ).tab( 'show' );
	} );
} );

