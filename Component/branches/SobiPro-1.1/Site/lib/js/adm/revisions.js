/**
 * Created with JetBrains PhpStorm.
 * User: neo
 * Date: 30/8/2013
 * Time: 19:36
 * To change this template use File | Settings | File Templates.
 */
SobiPro.jQuery( document ).ready( function ()
{
	SobiPro.jQuery( '.ctrl-revision-compare' ).click( function ( e )
	{
		e.preventDefault();
		SobiPro.jQuery( '#revisions-window' ).modal();
		SobiPro.jQuery( '.ctrl-diff' ).html( '<i class="icon-spinner icon-spin icon-large"></i>' );
		var request = {
			'option': 'com_sobipro',
			'task': 'entry.revisions',
			'sid': SobiPro.jQuery( '#SP_sid' ).val(),
			'format': 'raw',
			'tmpl': 'component',
			'method': 'xhr',
			'revision': SobiPro.jQuery( '#SP_revision' ).val(),
			'fid': SobiPro.jQuery( this ).data( 'fid' ),
			'html': 1
		}
		SobiPro.jQuery.ajax( {
			url: 'index.php',
			type: 'post',
			dataType: 'json',
			data: request
		} ).done( function ( response )
			{
				SobiPro.jQuery( '.ctrl-diff' ).html( response.diff );
			} );
	} );
} );

