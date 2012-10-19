/**
 * Created with JetBrains PhpStorm.
 * User: neo
 * Date: 18/10/2012
 * Time: 13:36
 * To change this template use File | Settings | File Templates.
 */

function SPCategoryChooser( opt )
{
	SobiPro.jQuery( document ).ready( function ()
	{
		SobiPro.jQuery( '#' + opt.id ).change( function ( e )
		{
			var selected = SobiPro.jQuery( this ).find( ':selected' );
			if ( selected.length > opt.limit ) {
				alert( SobiPro.Txt( 'FCC_LIMIT_REACHED' ).replace( '%d', opt.limit ) )
				for ( var i = opt.limit; i < selected.length; i++ ) {
					SobiPro.jQuery( selected[ i ] ).removeAttr( 'selected' );
				}
			}
		} );
	} );
}
