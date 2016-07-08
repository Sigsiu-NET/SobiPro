SobiPro.jQuery( function ()
{
	// initialize tooltips
	SobiPro.jQuery('[data-toggle="tooltip"]').tooltip();

	// initialize carousel slider
	SobiPro.jQuery( '#spCarousel' ).carousel();

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

	//resize the map, necessary if the map is in a collapsable element
	SobiPro.jQuery( '#address' ).on( 'shown.bs.collapse', function ( e )
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
