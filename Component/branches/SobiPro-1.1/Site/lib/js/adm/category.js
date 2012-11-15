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
		SobiPro.jQuery( '[name^="category.parent"]' ).val( SobiPro.jQuery( '#SP_selectedCid' ).val() );
		SobiPro.DebOut(SobiPro.jQuery( '[name^="category.parent"]' ));
		SobiPro.DebOut(SobiPro.jQuery( '[name^="category.parent"]' ).val());
		SobiPro.jQuery( '#categoryParentName' ).html( SobiPro.jQuery( '#SP_selectedCatName' ).val() );
	} );
	if ( SobiPro.jQuery( '#SP_categoryIconHolder' ).val() ) {
		SobiPro.jQuery( '#catIcoChooser' ).html( '<img src="' + SobiPro.jQuery( '#SP_categoryIconHolder' ).val() + '" style="max-width: 55px; max-height: 55px;" />' );
	}
	SobiPro.jQuery( '#catIcoChooser' ).click( function ()
	{
		var requestUrl = SobiPro.jQuery( this ).attr( 'rel' );
		SobiPro.jQuery( "#spIcoChooser" ).html( '<iframe id="spIcoSelectFrame" src="' + requestUrl + '" style="width: 650px; height: 500px; border: none;"> </iframe>' );
		SobiPro.jQuery( '#spIco' ).modal();
	} );
} );
function SPSelectIcon( src, name )
{
	SobiPro.jQuery( '#SP_categoryIconHolder' ).val( src );
	SobiPro.jQuery( '[name^="category.icon"]' ).val( name );
	if ( SobiPro.jQuery( '#SP_categoryIconHolder' ).val() ) {
		SobiPro.jQuery( '#catIcoChooser' ).html( '<img src="' + SobiPro.jQuery( '#SP_categoryIconHolder' ).val() + '" style="max-width: 55px; max-height: 55px;" />' );
	}
	SobiPro.jQuery( '#spIco' ).modal( 'hide' );
}
