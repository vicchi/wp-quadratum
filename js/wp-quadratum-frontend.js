(function($) {
	//console.log('wp-quadratum-frontend active');

	if (typeof WPQuadratum != 'undefined') {
		//console.log('WPQuadratum is defined; we have checkin metadata');
		var wp_quadratum_maps = {};
		
		if (typeof WPQuadratum.widgets != 'undefined') {
			$.each(WPQuadratum.widgets, function(index, widget) {
				//console.log('Index: ' + index);
				//console.log('Widget ID: ' + widget['id']);

				var map_div = 'wp-quadratum-widget-map-' + widget['instance'];
				var map_id = document.getElementById(map_div);
				
				render_plugin_map(map_id, widget['options']['zoom']);
				/*console.log('Creating map for ' + WPQuadratum.provider + ' in ' + map_div);
				var map = new mxn.Mapstraction(map_id, WPQuadratum.provider);

				var lat = WPQuadratum['checkin']['venue']['location']['lat'];
				var lng = WPQuadratum['checkin']['venue']['location']['lng'];
				console.log('Coords: ' + lat + ',' + lng);
				var coords = new mxn.LatLonPoint(lat, lng);
				map.setCenterAndZoom(coords, widget['options']['zoom']);
				var opts = {
					icon: WPQuadratum['icon-url'],
					iconSize: [32, 32]
				};
				var marker = new mxn.Marker(coords);
				marker.addData(opts);
				map.addMarker(marker);*/
			});
		}
		
		//else {
			//console.log('No widgets defined')
		//}

		$('div[id^=wp-quadratum-shortcode-map]').each(function() {
			//console.log('Found shortcode map div');
			//console.log($(this));
			//console.log($(this).id);
			//console.log(this.id);

			var elements = this.id.split('-');
			//console.log('Found ' + elements.length + ' elements');
			var instance = elements[elements.length - 1];
			//console.log('Instance: ' + instance);

			//var form_id = 'wp-quadratum-shortcode-form-' + instance;
			//console.log('Form ID: ' + form_id);
			var zoom_id = '#wp-quadratum-shortcode-zoom-' + instance;
			//console.log('Zoom ID: ' + zoom_id)
			var zoom = $(zoom_id).val();
			//console.log('Zoom level: ' + zoom);
			var map_id = 'wp-quadratum-shortcode-map-' + instance;
			//console.log('Target map ID: ' + map_id);
			render_plugin_map(map_id, zoom);
		});
	}
	//else {
		//console.log('WPQuadratum is undefined; no checkin metadata available');
	//}

	function render_plugin_map(id, zoom) {
		//console.log('render_plugin_map++');
		var map = new mxn.Mapstraction(id, WPQuadratum.provider);

		var lat = WPQuadratum['checkin']['venue']['location']['lat'];
		var lng = WPQuadratum['checkin']['venue']['location']['lng'];
		//console.log('Coords: ' + lat + ',' + lng);
		var coords = new mxn.LatLonPoint(lat, lng);
		map.setCenterAndZoom(coords, zoom);
		var opts = {
			icon: WPQuadratum['icon-url'],
			iconSize: [32, 32]
		};
		var marker = new mxn.Marker(coords);
		marker.addData(opts);
		map.addMarker(marker);
		//console.log('render_plugin_map--');
	}
	
})(jQuery);

