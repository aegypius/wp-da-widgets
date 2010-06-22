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
	define('PLUGIN_URL' , substr(PLUGIN_ROOT, strpos(PLUGIN_ROOT, '/wp-content/')));

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

		static $shortcode_enabled   = false;
		static $shortcodes          = array('da_gallery', 'da_favourites');

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

			include 'includes/templates/widget_form.php';

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

						// Creating Thumbnail cache
						if (preg_match_all('/\t?\ssrc="([^"]*\.(?:jpg|gif|png))"/x', $body, $m)) {

							switch (get_option('thumb-format')) {
								case IMG_PNG: $ext = 'png'; break;
								case IMG_GIF: $ext = 'gif'; break;
								case IMG_JPG: $ext = 'jpg'; break;
							}

							foreach ($m[1] as $picture) {

								$body = str_replace(
									$picture
									, sprintf(
										'%1$s/public/thumb.php?u=%2$s&w=%3$s&h=%4$s&f=%5$s" width="%3$spx" height="%4$spx'
										, get_bloginfo('wpurl') . PLUGIN_URL
										, $picture
										, get_option('thumb-size-x')
										, get_option('thumb-size-y')
										, get_option('thumb-format')
									)
									, $body
								);
							}
						}
					}

					echo $body;

					self::log("DEBUG[{$widget_id}] - Output content : {$body}");

					if ($cache) { $cache->end(array('DA_Widgets', 'cache_callback')); }
				}
				echo $after_widget;

			}
			catch(Exception $ex) {
				self::log("ERROR[{$widget_id}] - " . get_class($ex) . ' - ' . $ex->getMessage() . ' (' . $ex->getCode() . ')');
			}

			self::log(str_pad(" END {$widget_id} ", 72 , '-', STR_PAD_BOTH));

		}

		public static function cache_callback($buffer) {
			// Extracting parameters to generate thumb's filename
			if (preg_match_all('/src="([^"]+)"/xs', $buffer, $matches)) {
				foreach ($matches[1] as $url) {
					parse_str(parse_url($url, PHP_URL_QUERY), $args);

					if (empty($args['u']) || !$args['w'] || !$args['h'] || !$args['f'])
						continue;

					switch ($args['f']) {
						case IMG_PNG: $ext = 'png'; break;
						case IMG_GIF: $ext = 'gif'; break;
						case IMG_JPG: $ext = 'jpg'; break;
					}

					$thumbfile =  get_bloginfo('wpurl') . '/wp-content/cache' . DIRECTORY_SEPARATOR . 'da-widgets-' . sha1($args['u'] . $args['w'] . $args['h']) . '.' . $ext;
					$buffer = str_replace($url, $thumbfile, $buffer);
				}
			}
			return $buffer;
		}

		public static function log($message) {
			if (!self::$log_level)
				return;

			if (!is_string($message))
				throw Exception('Log messages must be strings !');
			error_log( strftime('%Y-%m-%d %H:%M:%S %Z') .' - '. rtrim($message, PHP_EOL) . PHP_EOL, 3, 'wp-content/cache' . DIRECTORY_SEPARATOR . 'da-widgets-' .strftime('%Y-%m-%d'). '.log');
		}

		public static function shortcodes($options, $content, $code) {
			global $wpdb;
			extract(shortcode_atts(array(
				'deviant' => null,
				'items' => 10,
				'rating' => null,
				'filter' => null,
				'sitback' => false
			), $options));

			if (intval($items) <= 0)
				return '';

			if ($sitback == 'flash' || $sitback == true) {

				$options = array(
					'title'               =>  $deviant . ($code =='da_favourites' ? "'s favourites" : "'s gallery") ,
					'rssQuery'            => ($code =='da_favourites' ? 'favby:' : 'gallery:') . $deviant,
					'resizableControls'   => 'true',
					'floatableControls'   => 'false',
					'disableTitleBar'     => 'false',
					'forceTitleBarWhenFS' => 'true',
					'autoPlay'            => 'true',
				);

				$gallery = sprintf(
					'<object type="application/x-shockwave-flash" data="%1$s" class="da-gallery">'.
						'<param name="movie" value="%1$s" />'.
						'<param name="wmode" value="transparent" />'.
						'<param name="quality" value="high" />'.
						'<param name="allowFullScreen" value="true" />'.
						'<param name="menu" value="false" />'.
					'</object>'
					,"http://st.deviantart.net/styles/swf/sitback.swf?v_0_9_3_76" . http_build_query($options)
				);
				return $gallery;
			}
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

				$thumb_size = 135;

				foreach ($slides as &$slide) {
					$thumbs[] = sprintf(
						'<li>'
							.'<a title="%2$s" href="%1$s">'
								.'<img class="thumb" src="%3$s" width="%4$spx" height="%5$spx" alt="Thumbnail for %2$s"/>'
							.'</a>'
							.'<div class="entry-title">%2$s</div>'
							.'%6$s'
							.'<br class="clear-fix" />'
						.'</li>'
						, $slide->content
						, $slide->title
						, get_bloginfo('wpurl') . '/' . self::thumb($slide->content, $thumb_size)
						, $thumb_size //get_option('thumb-size-x')
						, $thumb_size //get_option('thumb-size-y')

						// Entry Metas
						,sprintf(
							'<div class="entry-meta">'
								.'<span class="meta-prep meta-prep-author">%1$s%2$s</span>'
								.'<span class="author vcard">'
									.'<a class="url fn n" href="http://%3$s.deviantart.com" title="%4$s"><span class="avatar"><img src="%5$s" width="50px" height="50px" alt="%6$s"/></span>%3$s</a>'
								.'</span>'
								.'<span class="meta-sep"> - </span>'
								.'<span class="meta-prep meta-prep-entry-date">%7$s</span>'
								.'<span class="entry-date">'
									.'<abbr class="published" title="%8$s">%9$s</abbr>'
								.'</span>'
							.'</div>'
							, __('By ', 'da-widgets')
							, $slide->symbol
							, $slide->author
							, sprintf(__("View %s's profile", 'da-widgets'), $slide->author)
							, $slide->avatar
							, sprintf(__("%s's avatar", 'da-widgets'), $slide->author)
							, __('Published ', 'da-widgets')
							, date('Y-m-d\TH:i:sO', $slide->published)
							, date(get_option(date_format), $slide->published)
						)

					);
				}
			}

			$gallery = sprintf(
				'<div class="da-gallery">' .
					'<ul class="thumbs">%1$s</ul>' .
				'</div>'
				, join(PHP_EOL, $thumbs)
			);

			return $gallery;
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

		public static function thumb($picture, $width=null, $height=null, $format=null) {
			if (is_null($height)) $height = !is_null($width) ? $width : get_option('thumb-size-y');
			if (is_null($width))  $width  = get_option('thumb-size-x');
			if (is_null($format)) $format = get_option('thumb-format');

			switch ($format) {
				case IMG_PNG: $ext = 'png'; break;
				case IMG_GIF: $ext = 'gif'; break;
				case IMG_JPG: $ext = 'jpg'; break;
			}

			$thumbfile = 'wp-content/cache' . DIRECTORY_SEPARATOR . 'da-widgets-' . sha1($picture . $width . $height) . '.' . $ext;

			// TODO : Update this old image library
			if (!file_exists(ABSPATH . $thumbfile)) {
				$thumb = Image::CreateFromFile($picture);
				Image::Resize($thumb, $width * 2, $height == null ? null : $height * 2);

				Image::Crop($thumb, $width, $height, false, false, IMAGE_ALIGN_CENTER | IMAGE_ALIGN_CENTER);

				if (is_writeable(dirname(ABSPATH . $thumbfile))) {
					Image::Output($thumb, IMAGE_OUTPUTMODE_FILE, $format, ABSPATH . $thumbfile);
				} else {
					return false;
				}
			}

			return $thumbfile;
		}

		public static function scripts() {
			if (self::$shortcode_enabled) {
				wp_deregister_script('da-gallery');
				wp_register_script('slimbox2',   PLUGIN_URL . '/public/js/slimbox2.min.js', array('jquery'), '2.04');
				wp_register_script('da-gallery', PLUGIN_URL . '/public/js/gallery.min.js',  array('slimbox2'), self::VERSION);
				wp_enqueue_script('da-gallery');
			}
		}

		public static function styles() {
			wp_deregister_style('da-widgets');
			if (self::$shortcode_enabled) {
				wp_register_style('slimbox2',   PLUGIN_URL . '/public/css/slimbox2.css', array(),'2.04');
				wp_register_style('da-widgets', PLUGIN_URL . '/public/css/style.css', array('slimbox2'), self::VERSION);
			} else {
				wp_register_style('da-widgets', PLUGIN_URL . '/public/css/style.css', array(), self::VERSION);
			}
			wp_enqueue_style('da-widgets');
		}
	
		public static function shortcode_dependencies($posts) {
			if (empty($posts) || self::$shortcode_enabled) return $posts;
			$found = false;
			foreach ($posts as $post) {
				if (preg_match_all('/\[([^\]]+)\]/ms', $post->post_content, $matches)) {
					foreach ($matches[1] as $sc) {
						if (in_array(array_shift(explode(' ', $sc)), self::$shortcodes)) {
							self::$shortcode_enabled = true;
							return $posts;
						}
					}
				}
			}
			return $posts;
		}
	}

	// Register Widget
	add_action('widgets_init', create_function('', 'return register_widget("DA_Widgets");'));
	add_action('wp_head',      array('DA_Widgets', 'css'));
	#add_action('wp_head',      array('DA_Widgets', 'gallery_css'));

	// Register Javascripts & Stylesheets
	add_action('wp_print_scripts', array('DA_Widgets', 'scripts'));
	add_action('wp_print_styles',  array('DA_Widgets', 'styles'));

	// Register Shortcodes
	foreach (DA_Widgets::$shortcodes as $sc)
		add_shortcode($sc,    array('DA_Widgets', 'shortcodes'));

	// Setup I18n
	load_plugin_textdomain('da-widgets', PLUGIN_ROOT, basename(dirname(__FILE__)));

	// Filters for js/css inclusions
	add_filter('the_posts', array('DA_Widgets', 'shortcode_dependencies'));

	require_once realpath(dirname(__FILE__)).'/admin/admin.php';
}
