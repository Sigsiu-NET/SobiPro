/**
 * Created with JetBrains PhpStorm.
 * User: neo
 * Date: 14/8/2012
 * Time: 17:34
 * To change this template use File | Settings | File Templates.
 */

SobiPro.jQuery( document ).ready( function ()
{
	var count = 0;
	SobiPro.jQuery( '#SPAdmToolbar a' ).click( function ( e )
	{
		var task = SobiPro.jQuery( this ).attr( 'rel' );
		if ( task.length ) {
			e.preventDefault();
			if ( SobiPro.jQuery( '#SP_method' ).val() == 'xhr' ) {
				SobiPro.jQuery( '#SP_task' ).val( task );
				SobiPro.jQuery.ajax( {
					url:'index.php',
					data:SobiPro.jQuery( '#SPAdminForm' ).serialize(),
					type:'POST',
					dataType:'json',
					success:function ( data )
					{
						if ( !( data.redirect.execute ) ) {
							count++;
							alert = '<div class="alert alert-' + data.message.type + '"><a class="close" data-dismiss="alert" href="#">×</a><strong>' + count + '.&nbsp;</strong>' + data.message.text + '</div>';
							SobiPro.jQuery( '#spMessage' ).html( alert );
						}
						else {
							window.location.replace( data.redirect.url );
						}
					}
				} );
			}
			else {
				SobiPro.jQuery( '#SP_task' ).val( task );
				SobiPro.jQuery( '#SPAdminForm' ).submit();
			}
		}
	} );

	SobiPro.jQuery( '.spOrdering' ).change( function ()
	{
		SobiPro.jQuery( '#SPAdminForm' ).submit();
	} );

	SobiPro.jQuery( '[name="spToggle"]' ).change( function ()
	{
		SobiPro.jQuery( '[name="' + SobiPro.jQuery( this ).attr( 'rel' ) + '[]"]' ).prop( 'checked', SobiPro.jQuery( this ).is( ':checked' ) );
	} );
	SobiPro.jQuery( '[name="spReorder"]' ).click( function ( e )
	{
		e.preventDefault();
		SobiPro.jQuery( '#SP_task' ).val( SobiPro.jQuery( this ).attr( 'rel' ) + '.reorder' );
		SobiPro.jQuery( '#SPAdminForm' ).submit();
	} );
} );
