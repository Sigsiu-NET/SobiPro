/**
 * Created with JetBrains PhpStorm.
 * User: neo
 * Date: 19/1/2013
 * Time: 22:42
 * To change this template use File | Settings | File Templates.
 */
SobiPro.jQuery( document ).ready( function ()
{
	SobiPro.jQuery( '.search-query' ).typeahead( {
		source:function ( typeahead, query )
		{
			var request = { 'option':'com_sobipro', 'task':'search.suggest', 'sid':SobiProSection, 'term':query, 'format':'raw' };
			var proxy = this;
			return SobiPro.jQuery.ajax( {
				'type':'post',
				'url':'index.php',
				'data':request,
				'dataType':'json',
				success:function ( response )
				{
					responseData = [];
					if ( response.length ) {
						for ( var i = 0; i < response.length; i++ ) {
							responseData[ i ] = { 'name':response[ i ] };
						}
						typeahead.process( responseData );
						SobiPro.jQuery( proxy.$element ).after( SobiPro.jQuery( proxy.$menu ) );
					}
				}
			} );
		},
		onselect:function ( obj )
		{
			SobiPro.DebOut( this )
//			this.$element.value( obj.name );
			SobiPro.DebOut(SobiPro.jQuery( this.$element ).find( 'form' ))
		},
		property:"name"
	} );
} );
