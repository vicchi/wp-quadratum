(function($) {
	if (typeof WPQuadratum != 'undefined') {
		var wp_quadratum_maps = {};
		
		if (typeof WPQuadratum.widgets != 'undefined') {
			$.each(WPQuadratum.widgets, function(index, widget) {
				var map_div = 'wp-quadratum-widget-map-' + widget['instance'];
				var map_id = document.getElementById(map_div);
				
				render_plugin_map(map_id, widget['options']['zoom']);
			});
		}
		
		$('div[id^=wp-quadratum-shortcode-map]').each(function() {
			var elements = this.id.split('-');
			var instance = elements[elements.length - 1];

			var zoom_id = '#wp-quadratum-shortcode-zoom-' + instance;
			var zoom = $(zoom_id).val();
			var map_id = 'wp-quadratum-shortcode-map-' + instance;
			render_plugin_map(map_id, zoom);
		});
	}

	function render_plugin_map(id, zoom) {
		var map = new mxn.Mapstraction(id, WPQuadratum.provider);

		var lat = WPQuadratum['checkin']['venue']['location']['lat'];
		var lng = WPQuadratum['checkin']['venue']['location']['lng'];
		var coords = new mxn.LatLonPoint(lat, lng);
		map.setCenterAndZoom(coords, parseInt(zoom, 10));
		var opts = {
			icon: WPQuadratum['icon-url'],
			iconSize: [32, 32]
		};
		var marker = new mxn.Marker(coords);
		marker.addData(opts);
		map.addMarker(marker);
	}
	
})(jQuery);

