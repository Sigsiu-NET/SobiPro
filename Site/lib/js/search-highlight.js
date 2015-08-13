/**
 * Created by neo on 13/08/15.
 */
SobiPro.jQuery( document ).ready( function ()
{
	var Term = SobiPro.jQuery( '#SPSearchBox' ).val();
	var Words = Term.match( /("[^"]+"|[^"\s]+)/g );
	if ( Words.length ) {
		SobiPro.jQuery.each( Words, function ( i, word )
		{
			SobiPro.jQuery( '.entry-container' ).highlight( word );
		} );
	}
} );
