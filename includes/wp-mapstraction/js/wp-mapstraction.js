(function($) {
	if (typeof WPMapstraction != 'undefined') {
		//console.log('WPMapstraction is defined');
		$.each(WPMapstraction, function(map, args) {
			//console.log('Handling map type: ' + map);
			switch(map) {
				case 'googlev3':
					break;
				case 'nokia':
					//console.log('appId: ' + args['app-id']);
					//console.log('authenticationToken:' + args['auth-token']);
					nokia.Settings.set("appId", args['app-id']); 
					nokia.Settings.set("authenticationToken", args['auth-token']);
					break;
				case 'microsoft7':
					//console.log('key: ' + args['key']);
					window.microsoft_key = args['key'];
					break;
				default:
					break;
			}
		});
	}
})(jQuery);