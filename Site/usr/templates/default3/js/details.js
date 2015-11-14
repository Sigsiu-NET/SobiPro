SobiPro.jQuery( function ()
{
	SobiPro.jQuery( '.spHasTip' ).tooltip();

	// carousel slider
	SobiPro.jQuery( '#spCarousel' ).carousel(); //Initialisation
	SobiPro.jQuery( '#spCarousel' ).on( 'slid.bs.carousel', function ()
	{
	} );
	SobiPro.jQuery( '.spCarousel-target' ).on( 'click', function ()
	{
	} );

	//resize the map, necessary if the map is in a tab
	SobiPro.jQuery( '#tab_map' ).on( 'shown.bs.tab', function ( e )
	{
		try {
			var handler = SPGeoMapsReg[ jQuery( 'div[id^=field_map_canvas_]' ).attr( 'id' ) ];
			google.maps.event.trigger( handler.Map, 'resize' );
			handler.Map.setCenter( handler.Position );
		}
		catch ( e ) {
		}
	} );
} );
