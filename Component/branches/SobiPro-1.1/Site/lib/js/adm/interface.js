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
		SobiPro.jQuery( '#SP_task' ).val( task );
		if ( task.length ) {
			e.preventDefault();
			if ( SobiPro.jQuery( '#SP_method' ).val() == 'xhr' ) {
				req = SobiPro.jQuery( '#SPAdminForm' ).serialize();
				buttons = {};
				SobiPro.jQuery( SobiPro.jQuery( '#SPAdminForm' ).find( ':button' ) ).each( function ( i, b )
				{
					bt = SobiPro.jQuery( b );
					if ( bt.hasClass( 'active' ) ) {
						req += '&' + bt.attr( 'name' ) + '=' + bt.val();
					}
				} );
				SobiPro.jQuery( '#SP_task' ).val( task );
				SobiPro.jQuery.ajax( {
					url:'index.php',
					data: req,
					type:'post',
					dataType:'json',
					success:function ( data )
					{
						if ( !( data.redirect.execute ) ) {
							count++;
							c = '';
							if( count > 1 ) {
								c = '<strong>&nbsp;(&nbsp;' + count + '&nbsp;)</strong>';
							}
							alert = '<div class="alert alert-' + data.message.type + '"><a class="close" data-dismiss="alert" href="#">×</a>' + data.message.text + c +'</div>';
							SobiPro.jQuery( '#spMessage' ).html( alert );
						}
						else {
							window.location.replace( data.redirect.url );
						}
					}
				} );
			}
			else {
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
