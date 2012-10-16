/**
 * Created with JetBrains PhpStorm.
 * User: neo
 * Date: 14/8/2012
 * Time: 17:34
 * To change this template use File | Settings | File Templates.
 */

SobiPro.jQuery( document ).ready( function ()
{
	SpCcSwapMethod( SobiPro.jQuery( '#field-method' ).val() );
	SobiPro.jQuery( '#field-method' ).change( function ()
	{
		SpCcSwapMethod( SobiPro.jQuery( this ).val() );
	} );
	function SpCcSwapMethod( method )
	{
		SobiPro.jQuery( '.spCcMethod' ).hide();
		SobiPro.jQuery( '#spCc-' + method ).show();
		if ( method == 'fixed' ) {
			SobiPro.jQuery( '#field-editable :button' ).attr( 'disabled', 'disabled' );
			SobiPro.jQuery( '#field-catswithchilds :button' ).attr( 'disabled', 'disabled' );
			SobiPro.jQuery( '#field-editlimit' ).attr( 'disabled', 'disabled' );
		}
		else {
			SobiPro.jQuery( '#field-editable :button' ).removeAttr( 'disabled', 'disabled' );
			SobiPro.jQuery( '#field-catswithchilds :button' ).removeAttr( 'disabled', 'disabled' );
			SobiPro.jQuery( '#field-editlimit' ).removeAttr( 'disabled', 'disabled' );
		}
	}
} );
