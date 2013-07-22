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
	}
	
	/**
	 * Outputs the widget settings/options form on the Dashboard's Appearance -> Widgets
	 * screen
	 */
	
	function form($instance) {
		$text_stub = '<label for="%s">%s</label><input type="text" id="%s" name="%s" value="%s" class="widefat" />';
		$check_stub = '<input type="checkbox" id="%s" name="%s" %s /><label for="%s">%s</label>';
		$content = array();

		$instance = wp_parse_args (
			(array)$instance,
			array (
				'title' => __('Last Foursquare Checkin'),
				'width' => 200,
				'width_units' => 'px',
				'height' => 200,
				'height_units' => 'px',
				'zoom' => 16,
				'private' => 0,
				'id' => 1
				)
			);

		$content[] = '<p>'
			. sprintf ($text_stub,
				$this->get_field_id ('title'),
				__('Widget Title'),
				$this->get_field_id ('title'),
				$this->get_field_name ('title'),
				esc_attr ($instance['title'])
				)
			. '</p>';

		$content[] = '<p>'
			. sprintf ($text_stub,
				$this->get_field_id ('width'),
				__('Widget Width'),
				$this->get_field_id ('width'),
				$this->get_field_name ('width'),
				esc_attr ($instance['width'])
				)
			. '</p>';

		$label_stub = '<label for="%s">%s</label>';
		$select_start_stub = '<select id="%s" name="%s" class="widefat">';
		$select_end_stub = '</select>';
		$option_stub = '<option value="%s" %s>%s</option>';

		$content[] = '<p>';
		$content[] = sprintf($label_stub, $this->get_field_id('width_units'), 'Width Units');
		$content[] = sprintf($select_start_stub, $this->get_field_id('width_units'), $this->get_field_name('width_units'));
		$content[] = sprintf($option_stub, 'px', selected('px', $instance['width_units'], false), 'px');
		$content[] = sprintf($option_stub, '%', selected('%', $instance['width_units'], false), '%');
		$content[] = $select_end_stub;
		$content[] = '<p>';

		$content[] = '<p>'
			. sprintf ($text_stub,
				$this->get_field_id ('height'),
				__('Map Height'),
				$this->get_field_id ('height'),
				$this->get_field_name ('height'),
				esc_attr ($instance['height'])
				)
			. '</p>';

		$content[] = '<p>';
		$content[] = sprintf($label_stub, $this->get_field_id('height_units'), 'Height Units');
		$content[] = sprintf($select_start_stub, $this->get_field_id('height_units'), $this->get_field_name('height_units'));
		$content[] = sprintf($option_stub, 'px', selected('px', $instance['height_units'], false), 'px');
		$content[] = sprintf($option_stub, '%', selected('%', $instance['height_units'], false), '%');
		$content[] = $select_end_stub;
		$content[] = '<p>';

		$content[] = '<p>'
			. sprintf ($text_stub,
				$this->get_field_id ('zoom'),
				__('Map Zoom Level'),
				$this->get_field_id ('zoom'),
				$this->get_field_name ('zoom'),
				esc_attr ($instance['zoom'])
				)
			. '</p>';

		$content[] = '<p>'
			. sprintf ($check_stub,
				$this->get_field_id ('private'),
				$this->get_field_name ('private'),
				checked ($instance['private'], true, false),
				$this->get_field_id ('private'),
				__('Show Private Checkins')
				)
			. '</p>';

		echo implode('', $content);
	}
	
	/**
	 * Processes the widget settings/options form on the Dashboard's Appearance -> Widgets
	 * screen
	 */

	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		
		$instance['title'] = strip_tags ($new_instance['title']);
		$instance['width'] = (int)strip_tags ($new_instance['width']);
		$instance['width_units'] = strip_tags($new_instance['width_units']);
		$instance['height'] = (int)strip_tags ($new_instance['height']);
		$instance['height_units'] = strip_tags($new_instance['height_units']);
		$instance['zoom'] = (int)strip_tags ($new_instance['zoom']);
		if (isset ($instance['private'])) {
			$instance['private'] = (int)$new_instance['private'];
		}
		
		return $instance;
	}
	
	/**
	 * Outputs the contents of the widget on the front-end
	 */

	function widget($args, $instance) {
		extract ($args, EXTR_SKIP);
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
	}
	
	/**
	 * Outputs the contents of the checkin map within the widget
	 */
	
	function show_checkin_map($instance, $id) {
		$content = array ();

		$args = array ();
		$args['width'] = $instance['width'];
		$args['width_units'] = $instance['width_units'];
		$args['height'] = $instance['height'];
		$args['height_units'] = $instance['height_units'];
		$args['zoom'] = $instance['zoom'];
		if (isset ($instance['private'])) {
			$args['private'] = $instance['private'];
		}
		$args['container-class'] = 'wp-quadratum-widget-container';
		$args['container-id'] = 'wp-quadratum-widget-container-' . $id;
		$args['map-class'] = 'wp-quadratum-widget-map';
		$args['map-id'] = 'wp-quadratum-widget-map-' . $id;
		$args['venue-class'] = 'wp-quadratum-widget-venue';
		$content = WP_QuadratumFrontEnd::get_instance()->render_checkin_map ($args);
			
		return implode (PHP_EOL, $content);
	}
	
	private function get_widget_id($args) {
		$widget_id = $args['widget_id'];
		$widget_name = null;
		$widget_inst = null;
		
		$pos = strpos($widget_id, '-');
		if ($pos !== false) {
			$widget_name = substr($widget_id, 0, $pos);
			$widget_inst = substr($widget_id, ++$pos);
		}
		
		return $widget_inst;
	}
	
}	// end class WP_QuadratumWidget
?>