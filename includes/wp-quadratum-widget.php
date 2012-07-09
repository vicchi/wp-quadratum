<?php

require_once (WPQUADRATUM_PATH . '/foursquare-helper/foursquare-helper.php');

class WP_QuadratumWidget extends WP_Widget {
	function __construct() {
		$widget_ops = array (
			'description' => __('Displays your last Foursquare checkin')
			);
		parent::WP_Widget ('WP_QuadratumWidget', __('WP Quadratum'), $widget_ops);
		if (is_active_widget (false, false, $this->id_base)) {
			add_action ('template_redirect', array ($this, 'widget_external'));
		}
	}
	
	function widget_external() {
		wp_enqueue_script ('nokiamaps');
	}
	
	function form($instance) {
		$text_stub = '<label for="%s">%s</label><input type="text" id="%s" name="%s" value="%s" class="widefat" />';
		$check_stub = '<input type="checkbox" id="%s" name="%s" %s /><label for="%s">%s</label>';
		$content = '';

		$instance = wp_parse_args (
			(array)$instance,
			array (
				'title' => __('Last Foursquare Checkin'),
				'width' => 200,
				'height' => 200,
				'zoom' => 16,
				'private' => 0,
				'id' => 1
				)
			);

		$content = '<p>'
			. sprintf ($text_stub,
				$this->get_field_id ('title'),
				__('Widget Title'),
				$this->get_field_id ('title'),
				$this->get_field_name ('title'),
				esc_attr ($instance['title'])
				)
			. '</p>';

		$content .= '<p>'
			. sprintf ($text_stub,
				$this->get_field_id ('width'),
				__('Widget Width'),
				$this->get_field_id ('width'),
				$this->get_field_name ('width'),
				esc_attr ($instance['width'])
				)
			. '</p>';

		$content .= '<p>'
			. sprintf ($text_stub,
				$this->get_field_id ('height'),
				__('Map Height'),
				$this->get_field_id ('height'),
				$this->get_field_name ('height'),
				esc_attr ($instance['height'])
				)
			. '</p>';

		$content .= '<p>'
			. sprintf ($text_stub,
				$this->get_field_id ('zoom'),
				__('Map Zoom Level'),
				$this->get_field_id ('zoom'),
				$this->get_field_name ('zoom'),
				esc_attr ($instance['zoom'])
				)
			. '</p>';

		$content .= '<p>'
			. sprintf ($check_stub,
				$this->get_field_id ('private'),
				$this->get_field_name ('private'),
				checked ($instance['private'], true, false),
				$this->get_field_id ('private'),
				__('Show Private Checkins')
				)
			. '</p>';

		$content .= '<p>'
			. sprintf ($text_stub,
				$this->get_field_id ('id'),
				__('Widget Id'),
				$this->get_field_id ('id'),
				$this->get_field_name ('id'),
				esc_attr ($instance['id'])
				)
			. '</p>';
			
		echo $content;
	}
	
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		
		$instance['title'] = strip_tags ($new_instance['title']);
		$instance['width'] = (int)strip_tags ($new_instance['width']);
		$instance['height'] = (int)strip_tags ($new_instance['height']);
		$instance['zoom'] = (int)strip_tags ($new_instance['zoom']);
		if (isset ($instance['private'])) {
			$instance['private'] = (int)$new_instance['private'];
		}
		$instance['id'] = (int)strip_tags($new_instance['id']);
		
		return $instance;
	}
	
	function widget($args, $instance) {
		extract ($args, EXTR_SKIP);

		$content = $before_widget;
		if ($instance['title']) {
			$content .= $before_title
				. $instance['title']
				. $after_title;
		}
		$content .= $this->show_checkin_map ($instance);
		$content .= $after_widget;
		
		echo $content;
	}
	
	function show_checkin_map($instance) {
		$content = array ();

		$options = get_option ('wp_quadratum_settings');

		$json = WP_Quadratum::get_foursquare_checkins ();
		$checkins = $json->response->checkins->items;

		// TODO: Handle response caching

		foreach ($checkins as $checkin) {
			$app_id = NULL;
			$app_token = NULL;
			
			if (WP_Quadratum::is_wpna_installed () && WP_Quadratum::is_wpna_active ()) {
				$helper = new WPNokiaAuthHelper ();
				
				$tmp = $helper->get_id ();
				if (!empty ($tmp)) {
					$app_id = $tmp;
				}
				
				$tmp = $helper->get_token ();
				if (!empty ($tmp)) {
					$app_token = $tmp;
				}
			}
			
			else {
				$app_id = WP_Quadratum::get_option ('app_id');
				$app_token = WP_Quadratum::get_option ('app_token');
			}
			
			$args = array ();
			$args['width'] = $instance['width'];
			$args['height'] = $instance['height'];
			$args['zoom'] = $instance['zoom'];
			$args['private'] = $instance['private'];
			$args['app-id'] = $app_id;
			$args['app-token'] = $app_token;
			$args['container-class'] = 'wp-quadratum-widget-container';
			$args['container-id'] = 'wp-quadratum-widget-container-' . $instance['id'];
			$args['map-class'] = 'wp-quadratum-widget-map';
			$args['map-id'] = 'wp-quadratum-widget-map-' . $instance['id'];
			$args['venue-class'] = 'wp-quadratum-widget-venue';
			$args['checkin'] = $checkin;
			$content = WP_QuadratumFrontEnd::render_checkin_map ($args);
			
			break;	// Not really needed as we only return a single checkin item
		}

		return implode (PHP_EOL, $content);
	}
	
}	// end class WP_QuadratumWidget
?>