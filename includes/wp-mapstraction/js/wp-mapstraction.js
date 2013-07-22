(function($) {
	if (typeof WPMapstraction != 'undefined') {
		$.each(WPMapstraction, function(map, args) {
			switch(map) {
				case 'googlev3':
					break;
				case 'nokia':
					nokia.Settings.set("appId", args['app-id']); 
					nokia.Settings.set("authenticationToken", args['auth-token']);
					break;
				case 'microsoft7':
					window.microsoft_key = args['key'];
					break;
				default:
					break;
			}
		});
	}
})(jQuery);