/**
 * Created with JetBrains PhpStorm.
 * User: neo
 * Date: 14/8/2012
 * Time: 17:34
 * To change this template use File | Settings | File Templates.
 */

SobiPro.jQuery( document ).ready( function ()
{
	SobiPro.jQuery( '.spCategoryChooser' ).click( function ()
	{
		var requestUrl = SobiPro.jQuery( this ).attr( 'rel' );
		SobiPro.jQuery( "#spCatsChooser" ).html( '<iframe id="spCatSelectFrame" src="' + requestUrl + '" style="width: 100%; height: 100%; border: none;"> </iframe>' );
		SobiPro.jQuery( '#spCat' ).modal();
	} );
	SobiPro.jQuery( '#spCatSelect' ).bind( "click", function ( e )
	{
		if ( !( SobiPro.jQuery( '#SP_selectedCid' ).val() ) ) {
			return;
		}
		SobiPro.jQuery( '#selectedCatPath' ).html( SobiPro.jQuery( '#SP_selectedCatPath' ).val() );
		SobiPro.jQuery( '#categoryParent' ).val( SobiPro.jQuery( '#SP_selectedCid' ).val() );
		SobiPro.jQuery( '#categoryParentName' ).html( SobiPro.jQuery( '#SP_selectedCatName' ).val() );
	} );
} );
