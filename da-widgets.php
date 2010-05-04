<?php
/*
Plugin Name: deviantART widgets
Plugin URI: http://github.com/aegypius/wp-da-widgets
Description: This is a plugin which provide a widget to parse/display deviantART feeds
Author: Nicolas "aegypius" LAURENT
Version: 0.2
Author URI: http://www.aegypius.com
*/

if (class_exists('WP_Widget')) {

	define('PLUGIN_ROOT', realpath(dirname(__FILE__)));

	require_once PLUGIN_ROOT . '/includes/compat.php';
	require_once PLUGIN_ROOT . '/libraries/Cache.php';
	require_once PLUGIN_ROOT . '/libraries/Image.php';
	require_once PLUGIN_ROOT . '/libraries/DeviantArt/Log.php';
	require_once PLUGIN_ROOT . '/libraries/DeviantArt/Gallery.php';
	require_once PLUGIN_ROOT . '/libraries/DeviantArt/Favourite.php';

	class DA_Widgets extends WP_Widget {
		const VERSION               = '0.2';
		const DA_WIDGET_LOG         = 1;
		const DA_WIDGET_GALLERY     = 2;
		const DA_WIDGET_FAVOURITE   = 3;
		static $log_level           = 1;

		function DA_Widgets() {
			self::$log_level = get_option('debug-enabled');
			parent::WP_Widget(
				'da-widget',
				'deviantART',
				array(
					'description' =>  __('deviantART Feeds Integration', 'da-widgets'),
					'classname'   =>  'widget_da'
				)
			);
		}

		function form($instance) {

			$instance = wp_parse_args((array)$instance, array(
				'title'		=> 'deviantART',
				'type'		=> self::DA_WIDGET_LOG,
				'deviant'	=> '',
				'rating'	=> 'nonadult',
				'items'		=> 10,
				'html'		=> 1,
				'filter'	=> 0
			));

			$title		= esc_attr($instance['title']);
			$type		= intval($instance['type']);
			$deviant	= trim(esc_attr($instance['deviant']));
			$items		= intval($instance['items']);

			$rating		= esc_attr($instance['rating']);
			$html		= intval($instance['html']);
			$filter		= intval($instance['filter']);

	?>
		<p>
			<label for="<?php echo $this->get_field_id('title')?>"><?php _e('Title', 'da-widgets')?> : </label>
			<input class="widefat" type="text" id="<?php echo $this->get_field_id('title')?>" name="<?php echo $this->get_field_name('title')?>" value="<?php echo $title?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('type')?>"><?php _e('Content', 'da-widgets')?> : </label>
			<select class="widefat" id="<?php echo $this->get_field_id('type')?>" name="<?php echo $this->get_field_name('type')?>">
				<option <?php selected(self::DA_WIDGET_LOG, $type); ?> value="<?php echo self::DA_WIDGET_LOG?>">Journal</option>
				<option <?php selected(self::DA_WIDGET_GALLERY, $type); ?> value="<?php echo self::DA_WIDGET_GALLERY?>">Gallery</option>
				<option <?php selected(self::DA_WIDGET_FAVOURITE, $type); ?> value="<?php echo self::DA_WIDGET_FAVOURITE?>">Favourites</option>
			</select>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('deviant')?>"><?php _e('Deviant', 'da-widgets')?> : </label>
			<input class="widefat" type="text" id="<?php echo $this->get_field_id('deviant')?>" name="<?php echo $this->get_field_name('deviant')?>" value="<?php echo $deviant?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('items')?>"><?php _e('Items to display', 'da-widgets')?> : </label>
			<select class="widefat" id="<?php echo $this->get_field_id('items')?>" name="<?php echo $this->get_field_name('items')?>">

				<option <?php selected(-1 , $items) ?> value="-1"><?php _e('All', 'da-widgets')?></option>

			<?php foreach (range(1,10) as $v) : ?>
				<option <?php selected($v , $items) ?> value="<?php echo $v?>"><?php echo $v?></option>
			<?php endforeach; ?>
			</select>
		</p>

		<?php if ($type == self::DA_WIDGET_LOG) : ?>
		<p>
			<input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id('html')?>" name="<?php echo $this->get_field_name('html')?>" value="1" <?php if ( $html ) { echo 'checked="checked"'; } ?>/>
			<label for="<?php echo $this->get_field_id('html')?>"><?php _e('Keep original formating', 'da-widgets')?></label>
		</p>
		<?php else : ?>
		<p>
			<label for="<?php echo $this->get_field_id('rating')?>"><?php _e('Content Rating', 'da-widgets')?> : </label>
			<select class="widefat" id="<?php echo $this->get_field_id('rating')?>" name="<?php echo $this->get_field_name('rating')?>">
				<option <?php selected('nonadult', $rating); ?> value="nonadult"><?php _e('Forbid adult content', 'da-widgets')?></option>
				<option <?php selected('all', $rating); ?> value="all"><?php _e('Allow adult content', 'da-widgets')?></option>
			</select>
		</p>
<?php
			switch ($type) {
				case self::DA_WIDGET_GALLERY:
					$res = new DeviantArt_Gallery($deviant, $rating, $scraps);
					break;
				default:
				case self::DA_WIDGET_FAVOURITE:
					$res = new DeviantArt_Favourite($deviant, $rating, $scraps);
					break;
			}

			foreach ($res->getCategories() as $categoryId=>$categoryName) {
				$options .= sprintf('<option value="%s"%s>%s</option>', $categoryId, ($categoryId == $filter ? ' selected="selected"' : ''), $categoryName);
			}
?>
		<p>
			<label for="<?php echo $this->get_field_id('filter')?>"><?php _e('Category filter', 'da-widgets')?> : </label>
			<select class="widefat" id="<?php echo $this->get_field_id('filter')?>" name="<?php echo $this->get_field_name('filter')?>">
				<option <?php selected(0, $filter); ?> value="0"><?php _e('Disabled', 'da-widgets')?></option>
				<?php echo $options ?>
				<option <?php selected(-1, $filter); ?> value="-1"><?php _e('Scraps', 'da-widgets')?></option>
			</select>
		</p>

		<?php endif; ?>


	<?php
		}

		function css() {
			if (get_option('user-css')) {
				$css = get_option('user-css');
			} else {
				$css = 'ul.da-widgets{list-style:none;margin:0;text-align:center;}'
					  .'ul.da-widgets li{display:inline;}'
					  .'ul.da-widgets li a{display:inline-block;padding:3px;margin:2px;border: 1px solid #ececec;background-color: #fff}'
					  .'ul.da-widgets li a:hover{border:1px solid #ccc;}';
			}
			printf('<style type="text/css">%s</style>', $css);
		}

		function widget($args, $instance) {
			try {
				extract($args, EXTR_SKIP);

				self::log(str_pad(" BEGIN {$widget_id} ", 72, '-', STR_PAD_BOTH));
				self::log("DEBUG[{$widget_id}] - Frontend Initalization");

				$title   = esc_attr($instance['title']);
				$type    = esc_attr($instance['type']);
				$deviant = esc_attr($instance['deviant']);
				$items   = intval($instance['items']);
				$rating  = esc_attr($instance['rating']);
				$html    = intval($instance['html']);
				$filter  = intval($instance['filter']);

				self::log("DEBUG[{$widget_id}] - Cache is "       . (get_option('cache-enabled') ? 'enabled' : 'disabled') . " (duration : " . get_option('cache-duration') . ")");
				self::log("DEBUG[{$widget_id}] - Thumnbails are " . (get_option('thumb-enabled') ? 'enabled' : 'disabled') . ' (size : ' . get_option('thumb-size-x') . 'x'. get_option('thumb-size-y') .')');
				self::log("DEBUG[{$widget_id}] - Config : "       . print_r($instance, true));

				echo $before_widget;
				echo $before_title . $title . $after_title;

				if (get_option('cache-enabled')) {
					$fragment = 'wp-content/cache' . DIRECTORY_SEPARATOR . 'da-widgets-' . sha1(serialize($instance)) . '.html.gz';
					$duration = sprintf('+%d minutes', get_option('cache-duration'));
					$cache = new Cache(ABSPATH . $fragment, $duration);

					self::log("DEBUG[{$widget_id}] - Cache fragment : "       . $fragment);
				}

				if (!$cache || $cache->start()) {
					self::log("DEBUG[{$widget_id}] - Generating content");

					switch ($type) {
						case self::DA_WIDGET_LOG:
							$res = new DeviantArt_Log($deviant, $html);
							$body = $res->get($items);
							break;
						case self::DA_WIDGET_GALLERY:
							$res = new DeviantArt_Gallery($deviant);
							$body = $res->get($items, $rating, $filter);
							break;
						case self::DA_WIDGET_FAVOURITE:
							$res = new DeviantArt_Favourite($deviant);
							$body = $res->get($items, $rating, $filter);
							break;
					}

					self::log("DEBUG[{$widget_id}] - Preparing content : {$body}");

					if (in_array($type, array(self::DA_WIDGET_GALLERY, self::DA_WIDGET_FAVOURITE)) && get_option('thumb-enabled')) {

						self::log("DEBUG[{$widget_id}] - Generating thumbnails");

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

								self::log("DEBUG[{$widget_id}] - > " . $picture . ' => ' . $thumbfile);

								if (is_file(ABSPATH . $thumbfile)) {
									$body = str_replace(
										$picture
										, get_bloginfo('wpurl') . '/' . $thumbfile .'" width="' . get_option('thumb-size-x') .'px" height="' . get_option('thumb-size-y') . 'px'
										, $body
									);
								}

							}
						}
					}

					echo $body;

					self::log("DEBUG[{$widget_id}] - Output content : {$body}");

					if ($cache) {
						$cache->end();
					}
				}
				echo $after_widget;

			}
			catch(Exception $ex) {
				self::log("ERROR[{$widget_id}] - " . get_class($ex) . ' - ' . $ex->getMessage() . ' (' . $ex->getCode() . ')');
			}

			self::log(str_pad(" END {$widget_id} ", 72 , '-', STR_PAD_BOTH));

		}

		public static function log($message) {
			if (!self::$log_level)
				return;

			if (!is_string($message))
				throw Exception('Log messages must be strings !');
			error_log( strftime('%Y-%m-%d %H:%M:%S %Z') .' - '. rtrim($message, PHP_EOL) . PHP_EOL, 3, 'wp-content/cache' . DIRECTORY_SEPARATOR . 'da-widgets-' .strftime('%Y-%m-%d'). '.log');
		}

		public static function gallery($options, $content, $code) {
			global $wpdb;
			extract(shortcode_atts(array(
				'deviant' => null,
				'items' => 10,
				'rating' => null,
				'filter' => null,
			), $options));

			// if deviantID is null we look for existing 
			// widgets and put them in the list
			if (is_null($deviant)) {
				$widgets_instances = $wpdb->get_var(
					"SELECT option_value"
					." FROM {$wpdb->options}"
					." WHERE option_name='widget_da-widget'"
					." LIMIT 1"
				);

				if (!is_null($widgets_instances)) {
					$instances = unserialize($widgets_instances);
					foreach ($instances as $instance) {
						if (isset($instance['deviant']) && $instance['type'] == self::DA_WIDGET_GALLERY)
							$deviants[] = $instance['deviant'];
					}
				}
			} else {
				$deviants = explode(',', $deviant);
			}

			$nav = array();
			$slides = array();

			foreach ($deviants as $d) {
				switch ($code) {
					case 'da_favourites' : 
						$res = new DeviantArt_Favourite($d);
						break;
					default:
						$res = new DeviantArt_Gallery($d);
				}
				$slides = $res->get($items, $rating, $filter, false);
				foreach ($slides as &$slide) {
					$nav[] = sprintf(
						'<li><a title="%2$s" href="#%1$s">%2$s</a></li>'
						, 'da-widgets-' .self::slugify($slide->title)
						, $slide->title
					);
					$slide = sprintf(
						'<li id="%1$s"><img src="%3$s" alt="%2$s" title="%2$s" /></li>'
						, 'da-widgets-' . self::slugify($slide->title)
						, $slide->title
						, $slide->content
					);
				}
			}

			$gallery = sprintf(
				'<div class="da-gallery">' .
					'<ol class="nav">%s</ol>' .
					'<ul class="slides">%s</ul>' .
				'</div>'
				, join(PHP_EOL, $nav)
				, join(PHP_EOL, $slides)
			);

			return $gallery;
		}

		public static function gallery_css() {
			$css = <<<CSS
.da-gallery {position: relative; width: 470px; height: 480px; overflow: hidden;}
.da-gallery .slides, .da-gallery .nav {list-style: none; margin: 0; padding: 0;}
.da-gallery .slides li {position: absolute; top: 0; left: 0; z-index: 0; margin: 0;padding: 0}
.da-gallery .slides li img {opacity: 0; filter: alpha(opacity=0)}
.da-gallery .slides li:first-child,
.da-gallery .slides li:target {z-index: 5}
.da-gallery .slides li:first-child img,
.da-gallery .slides li:target img {opacity: 1; filter: alpha(opacity=100)}
.da-gallery .nav {bottom: 2px; left: 0px; position: absolute; z-index: 10; text-align: center; width: 100%;}
.da-gallery .nav li {display: inline;margin: 0}
.da-gallery .nav li a {margin: 2px; outline: none;display: inline-block; background: #fff; width: 30px; height: 30px; text-indent: -10000em; opacity: .3; border: 1px solid black;}
.da-gallery .nav li a:active {opacity: .7}
CSS;
			printf('<style type="text/css">%s</style>', $css);
		}

		public static function slugify($text, $sep = '-') {
			// replace non letter or digits by $sep
			$text = preg_replace('/[^\\pL\d]+/u', $sep, $text);

			// trim 
			$text = trim($text, ' '.$sep);

			// transliterate
			if (function_exists('iconv')) {
				$test = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
			}

			// lowercase
			$text = strtolower($test);

			// remove unwanted characters
			$text = preg_replace('/[^' .$sep. '\w]+/', '', $text);

			if (empty($text))
				return "n{$sep}a";
			return $text;
		}
	}

	// Register Widget
	add_action('widgets_init', create_function('', 'return register_widget("DA_Widgets");'));
	add_action('wp_head',      array('DA_Widgets', 'css'));
	add_action('wp_head',      array('DA_Widgets', 'gallery_css'));

	// Register Shortcode
	add_shortcode('da_gallery',    array('DA_Widgets', 'gallery'));
	add_shortcode('da_favourites', array('DA_Widgets', 'gallery'));

	// Setup I18n
	load_plugin_textdomain('da-widgets', PLUGIN_ROOT, basename(dirname(__FILE__)));

	require_once realpath(dirname(__FILE__)).'/admin/admin.php';
}
