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
//		SobiPro.jQuery( '#revision-current' ).html( '<i class="icon-spinner icon-spin icon-large"></i>' );
//		SobiPro.jQuery( '#revision-loaded' ).html( '<i class="icon-spinner icon-spin icon-large"></i>' );
		SobiPro.jQuery( '#revisions-window' ).modal();
		var request = {
			'option': 'com_sobipro',
			'task': 'field.revisions',
			'sid': SobiPro.jQuery( '#SP_sid' ).val(),
			'format': 'raw',
			'tmpl': 'component',
			'method': 'xhr',
			'revision': SobiPro.jQuery( '#SP_revision' ).val(),
			'fid': SobiPro.jQuery( this ).data( 'fid' )
		}
		SobiPro.jQuery.ajax( {
			url: 'index.php',
			type: 'post',
			dataType: 'json',
			data: request
		} ).done( function ( response )
			{
				SobiPro.jQuery( '#revision-current' ).html( response.current );
				SobiPro.jQuery( '#revision-loaded' ).html( response.revision );
				SobiPro.jQuery( '.ctrl-diff' ).prettyTextDiff( { 'cleanup': true } );
			} );
	} );
} );

