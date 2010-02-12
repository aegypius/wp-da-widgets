<?php
/*
Plugin Name: deviantART widgets
Plugin URI: http://github.com/aegypius/wp-da-widgets
Description: This is a plugin which provide a widget to parse/display deviantART feeds
Author: Nicolas "aegypius" LAURENT
Version: 0.1.3
Author URI: http://www.aegypius.com
*/

if (class_exists('WP_Widget')) {

	require_once realpath(dirname(__FILE__)).'/includes/compat.php';
	require_once realpath(dirname(__FILE__)).'/libraries/Cache.php';
	require_once realpath(dirname(__FILE__)).'/libraries/Image.php';
	require_once realpath(dirname(__FILE__)).'/libraries/DeviantArt/Log.php';
	require_once realpath(dirname(__FILE__)).'/libraries/DeviantArt/Gallery.php';
	require_once realpath(dirname(__FILE__)).'/libraries/DeviantArt/Favourite.php';

	class DA_Widgets extends WP_Widget {
		const VERSION               = '0.1.3';
		const DA_WIDGET_LOG         = 1;
		const DA_WIDGET_GALLERY     = 2;
		const DA_WIDGET_FAVOURITE   = 3;
		const MODE_DEBUG            = true;

		function DA_Widgets() {
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
				'rating'	=> 'nonadult',
				'items'		=> 10,
				'html'		=> 1
			));

			$title		= esc_attr($instance['title']);
			$type		= intval($instance['type']);
			$deviant	= trim(esc_attr($instance['deviant']));
			$items		= intval($instance['items']);

			$html		= intval($instance['html']);
			$rating		= esc_attr($instance['rating']);

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
			<label for="<?php echo $this->get_field_id('items')?>">Items to display : </label>
			<select class="widefat" id="<?php echo $this->get_field_id('items')?>" name="<?php echo $this->get_field_name('items')?>">
				<option <?php selected(-1 , $items) ?> value="-1"><?php _e('All')?></option>
			<?php foreach (range(1,10) as $v) : ?>
				<option <?php selected($v , $items) ?> value="<?php echo $v?>"><?php echo $v?></option>
			<?php endforeach; ?>
			</select>
		</p>

		<?php if ($type == self::DA_WIDGET_LOG) : ?>
		<p>
			<input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id('html')?>" name="<?php echo $this->get_field_name('html')?>" value="1" <?php if ( $html ) { echo 'checked="checked"'; } ?>/>
			<label for="<?php echo $this->get_field_id('html')?>">Keep original formating</label>
		</p>
		<?php else : ?>
		<p>
			<label for="<?php echo $this->get_field_id('rating')?>">Content Rating : </label>
			<select class="widefat" id="<?php echo $this->get_field_id('rating')?>" name="<?php echo $this->get_field_name('rating')?>">
				<option <?php selected('nonadult', $rating); ?> value="nonadult"><?php _e('Forbid adult content')?></option>
				<option <?php selected('all', $rating); ?> value="all"><?php _e('Allow adult content')?></option>
			</select>
		</p>
		<?php endif; ?>


	<?php
		}

		function css() {
?>
<style type="text/css">
ul.da-widgets.favourite,
ul.da-widgets.gallery      { list-style: none; margin: 0; text-align: center;}
ul.da-widgets.favourite li,
ul.da-widgets.gallery li   { display: inline; }
ul.da-widgets.favourite a,
ul.da-widgets.gallery a    { display: inline-block; padding: 5px 5px; }
ul.da-widgets.favourite a,
ul.da-widgets.gallery a    { display: inline-block; padding: 3px 3px; margin: 2px 2px;border: 1px solid #CCC; }
</style>
<?php
		}

		function widget($args, $instance) {
			try {
				extract($args, EXTR_SKIP);
				$title = esc_attr($instance['title']);
				$type = esc_attr($instance['type']);
				$deviant = esc_attr($instance['deviant']);
				$html = intval($instance['html']);
				$items = intval($instance['items']);
				$rating = esc_attr($instance['rating']);

				echo $before_widget;
				echo $before_title . $title . $after_title;

				if (get_option('cache-enabled')) {
					$fragment = 'wp-content/cache' . DIRECTORY_SEPARATOR . 'da-widgets-' . sha1(serialize($instance)) . '.html.gz';
					$duration = sprintf('+%d minutes', get_option('cache-duration'));
					$cache = new Cache(ABSPATH . $fragment, $duration);
				}

				if (!$cache || $cache->start()) {
					switch ($type) {
						case self::DA_WIDGET_LOG:
							$res = new DeviantArt_Log($deviant, $html);
							$body = $res->get($items);
							break;
						case self::DA_WIDGET_GALLERY:
							$res = new DeviantArt_Gallery($deviant, $rating);
							$body = $res->get($items);
							break;
						case self::DA_WIDGET_FAVOURITE:
							$feed = new DeviantArt_Favourite($deviant, $rating);
							$body = $feed->get($items);
							break;
					}

					if (in_array($type, array(self::DA_WIDGET_GALLERY, self::DA_WIDGET_FAVOURITE)) && get_option('thumb-enabled')) {

						// Creating Thumbnail cache
						if (preg_match_all('/\t?\ssrc="([^"]*\.(?:jpg|gif|png))"/x', $body, $m)) {

							switch (get_option('thumb-format')) {
								case IMG_PNG: $ext = 'png'; break;
								case IMG_GIF: $ext = 'gif'; break;
								case IMG_JPG: $ext = 'jpg'; break;
							}

							foreach ($m[1] as $picture) {

								$thumbfile = 'wp-content/cache' . DIRECTORY_SEPARATOR . 'da-widgets-' . sha1($picture) . '.' . $ext;

								// TODO : Update this old image library
								if (!file_exists(ABSPATH . $thumbfile)) {
									$thumb = Image::CreateFromFile($picture);
									Image::Resize($thumb
										, get_option('thumb-size-x') * 2
										, get_option('thumb-size-y') * 2
									);

									Image::Crop($thumb
										, get_option('thumb-size-x')
										, get_option('thumb-size-y')
										, false
										, false
										, IMAGE_ALIGN_CENTER | IMAGE_ALIGN_CENTER
									);

									if (is_writeable(dirname(ABSPATH . $thumbfile))) {
										Image::Output($thumb
											, IMAGE_OUTPUTMODE_FILE
											, get_option('thumb-format')
											, ABSPATH . $thumbfile
										);
									}
								}

								if (is_file(ABSPATH . $thumbfile)) {
									$body = str_replace(
										$picture
										, get_bloginfo('wpurl') . '/' . $thumbfile
										, $body
									);
								}

							}
						}
					}

					echo $body;

					if ($cache) {
						$cache->end();
					}
				}
				echo $after_widget;

			}
			catch(Exception $ex) {
				if (self::MODE_DEBUG) 
					printf ('%s : %s (%s)', get_class($ex), $ex->getMessage(), $ex->getCode());
			}

		}
	}

	add_action('widgets_init',			create_function('', 'return register_widget("DA_Widgets");'));
	add_action('wp_head',				array('DA_Widgets', 'css'));
	require_once realpath(dirname(__FILE__)).'/admin/admin.php';
}
?>
