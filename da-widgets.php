<?php
/*
Plugin Name: deviantART widgets
Plugin URI: http://www.aegypius.com/
Description: This is a plugin which provide a widget to parse/display deviantART feeds
Author: Nicolas "aegypius" LAURENT
Version: 0.1
Author URI: http://www.aegypius.com
*/

require_once realpath(dirname(__FILE__)).'/includes/compat.php';
require_once realpath(dirname(__FILE__)).'/libraries/DeviantArt/Log.php';
require_once realpath(dirname(__FILE__)).'/libraries/DeviantArt/Gallery.php';
require_once realpath(dirname(__FILE__)).'/libraries/DeviantArt/Favourite.php';

class DA_Widget extends WP_Widget {
	const VERSION				= '0.1';
	const DA_WIDGET_LOG			= 1;
	const DA_WIDGET_GALLERY		= 2;
	const DA_WIDGET_FAVOURITE	= 3;
	
	function DA_Widget() {
		parent::WP_Widget(
			'da-widget',
			'deviantART',
			array(
				'description' =>  __('deviantART Feeds Integration'),
				'classname'   =>  'widget_da'
			)
		);
	}

	function form($instance) {

		$instance = wp_parse_args((array)$instance, array(
			'title'		=> 'deviantArt',
			'type'		=> self::DA_WIDGET_LOG,
			'deviant'	=> '',
			'html'		=> 1
		));

		$title		= esc_attr($instance['title']);
		$type		= intval($instance['type']);
		$deviant	= esc_attr($instance['deviant']);
		$html		= intval($instance['poll_id']);

?>
	<p>
		<label for="<?php echo $this->get_field_id('title')?>">Title : </label>
		<input class="widefat" type="text" id="<?php echo $this->get_field_id('title')?>" name="<?php echo $this->get_field_name('title')?>" value="<?php echo $title?>" />
	</p>
	<p>
		<label for="<?php echo $this->get_field_id('type')?>">Content : </label>
		<select class="widefat" id="<?php echo $this->get_field_id('type')?>" name="<?php echo $this->get_field_name('type')?>">
			<option <?php selected(self::DA_WIDGET_LOG, $type); ?> value="<?php echo self::DA_WIDGET_LOG?>">Journal</option>
			<option <?php selected(self::DA_WIDGET_GALLERY, $type); ?> value="<?php echo self::DA_WIDGET_GALLERY?>">Gallery</option>
			<option <?php selected(self::DA_WIDGET_FAVOURITE, $type); ?> value="<?php echo self::DA_WIDGET_FAVOURITE?>">Favourites</option>
		</select>
	</p>
	<p>
		<label for="<?php echo $this->get_field_id('deviant')?>">Deviant : </label>
		<input class="widefat" type="text" id="<?php echo $this->get_field_id('deviant')?>" name="<?php echo $this->get_field_name('deviant')?>" value="<?php echo $deviant?>" />
	</p>
	<p>
		<input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id('html')?>" name="<?php echo $this->get_field_name('html')?>" value="1" <?php if ( $html ) { echo 'checked="checked"'; } ?>/>
		<label for="<?php echo $this->get_field_id('html')?>">Keep original formating</label>
	</p>


<?php
	}

	function widget($args, $instance) {
		extract($args, EXTR_SKIP);
		$title = esc_attr($instance['title']);

		echo $before_widget;
		echo $before_title . $title . $after_title;

		if ($this->cache_begin($instance)) {

			$this->cache_end();
		}

		echo $after_widget;
	}

	function cache_begin($instance, $duration = '+10 minutes') {
		return true;
	}

	function cache_end() {
		return false;
	}

}

add_action('widgets_init',			create_function('', 'return register_widget("DA_Widget");'));
require_once realpath(dirname(__FILE__)).'/admin/admin.php';
?>
