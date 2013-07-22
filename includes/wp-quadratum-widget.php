<?php

/**
 * WP_QuadratumWidget - handles the widget for the plugin
 */

require_once (WPQUADRATUM_PATH . '/includes/foursquare-helper/foursquare-helper.php');

class WP_QuadratumWidget extends WP_Widget {
	function __construct() {
		$widget_ops = array (
			'description' => __('Displays your last Foursquare checkin')
			);
		parent::WP_Widget ('WP_QuadratumWidget', __('WP Quadratum'), $widget_ops);
		/*if (is_active_widget (false, false, $this->id_base)) {
			add_action ('template_redirect', array ($this, 'widget_external'));
		}*/
	}
	
	/*function widget_external() {
		wp_enqueue_script ('nokiamaps');
	}*/
	
	/**
	 * Outputs the widget settings/options form on the Dashboard's Appearance -> Widgets
	 * screen
	 */
	
	function form($instance) {
		//error_log('wp-quadratum-widget::form++');
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

		echo $content;
		//error_log('wp-quadratum-widget::form--');
	}
	
	/**
	 * Processes the widget settings/options form on the Dashboard's Appearance -> Widgets
	 * screen
	 */

	function update($new_instance, $old_instance) {
		//error_log('wp-quadratum-widget::update++');
		$instance = $old_instance;
		
		$instance['title'] = strip_tags ($new_instance['title']);
		$instance['width'] = (int)strip_tags ($new_instance['width']);
		$instance['height'] = (int)strip_tags ($new_instance['height']);
		$instance['zoom'] = (int)strip_tags ($new_instance['zoom']);
		if (isset ($instance['private'])) {
			$instance['private'] = (int)$new_instance['private'];
		}
		
		//error_log('wp-quadratum-widget::update--');
		return $instance;
	}
	
	/**
	 * Outputs the contents of the widget on the front-end
	 */

	function widget($args, $instance) {
		//error_log('wp-quadratum-widget::widget++');
		extract ($args, EXTR_SKIP);

		//error_log('++Instance Dump++');
		//error_log(var_export($instance, true));
		//error_log('--Instance Dump--');
		//error_log('++Args Dump++');
		//error_log(var_export($args, true));
		//error_log('--Args Dump--');

		$id = $this->get_widget_id($args);
		
		$content = $before_widget;
		if ($instance['title']) {
			$content .= $before_title
				. $instance['title']
				. $after_title;
		}
		$content .= $this->show_checkin_map ($instance, $id);
		$content .= $after_widget;
		
		echo $content;
		//error_log('wp-quadratum-widget::widget--');
	}
	
	/**
	 * Outputs the contents of the checkin map within the widget
	 */
	
	function show_checkin_map($instance, $id) {
		//error_log('wp-quadratum-widget::show_checkin_map++');
		$content = array ();

		//$options = get_option ('wp_quadratum_settings');

		//$json = WP_Quadratum::get_instance()->get_foursquare_checkins ();
		//$checkins = $json->response->checkins->items;

		// TODO: Handle response caching

		//foreach ($checkins as $checkin) {
			$args = array ();
			$args['width'] = $instance['width'];
			$args['height'] = $instance['height'];
			$args['zoom'] = $instance['zoom'];
			if (isset ($instance['private'])) {
				$args['private'] = $instance['private'];
			}
			$args['container-class'] = 'wp-quadratum-widget-container';
			//$args['container-id'] = 'wp-quadratum-widget-container-' . $instance['id'];
			$args['container-id'] = 'wp-quadratum-widget-container-' . $id;
			$args['map-class'] = 'wp-quadratum-widget-map';
			//$args['map-id'] = 'wp-quadratum-widget-map-' . $instance['id'];
			$args['map-id'] = 'wp-quadratum-widget-map-' . $id;
			$args['venue-class'] = 'wp-quadratum-widget-venue';
			//$args['checkin'] = $checkin;
			$content = WP_QuadratumFrontEnd::get_instance()->render_checkin_map ($args);
			
			//break;	// Not really needed as we only return a single checkin item
		//}

		//error_log('wp-quadratum-widget::show_checkin_map--');
		return implode (PHP_EOL, $content);
	}
	
	private function get_widget_id($args) {
		$widget_id = $args['widget_id'];
		$widget_name = null;
		$widget_inst = null;
		
		$pos = strpos($widget_id, '-');
		if ($pos !== false) {
			//error_log('Well formed widget name');
			$widget_name = substr($widget_id, 0, $pos);
			//error_log('Widget name: ' . $widget_name);
			$widget_inst = substr($widget_id, ++$pos);
			//error_log('Widget instance: ' . $widget_inst);
		}
		
		return $widget_inst;
	}
	
}	// end class WP_QuadratumWidget
?>