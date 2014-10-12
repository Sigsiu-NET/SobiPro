/**
 * Created with JetBrains PhpStorm.
 * User: neo
 * Date: 10/8/2013
 * Time: 20:44
 * To change this template use File | Settings | File Templates.
 */
SobiPro.jQuery( document ).ready( function ()
{
	var template = '<div class="popover"><div class="arrow"></div><div class="popover-inner"><div class="pull-right close spclose">x</div><h3 class="popover-title"></h3><div class="popover-content"><p></p></div></div></div>';
	SobiPro.jQuery( '[data-toggle=popover]' )
		.popover( { 'html': true, 'trigger': 'click', 'placement': 'top', 'template': template } )
		.click( function ( e )
		{
			e.preventDefault();
			var proxy = SobiPro.jQuery( this );
			SobiPro.jQuery( this ).parent().find( '.popover' ).find( '.close' ).click( function ()
			{
				proxy.popover( 'hide' );
			} )
		} );
} );

