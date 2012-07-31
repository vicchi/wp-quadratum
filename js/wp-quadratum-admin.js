(function($) {
	$().ready(function() {
		$('#wp-quadratum-map-provider').focus (function () {
			$(this).data ('prev-provider', $(this).val ());
		});
		$('#wp-quadratum-map-provider').change (function() {
			var prev_id = $('#wp-quadratum-' + $(this).data ('prev-provider') + '-settings');
			var curr_id = $('#wp-quadratum-' + $(this).val () + '-settings');
			prev_id.toggle ();
			curr_id.toggle ();
			$(this).data ('prev-provider', $(this).val ());
		});
	});
})(jQuery);