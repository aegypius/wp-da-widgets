<?php

add_action('admin_head',			'da_widgets_admin_head');
add_action('admin_menu',			'da_widgets_admin_menu');
add_filter('plugin_action_links',	'da_widgets_action_links', 9, 2);
add_filter('plugin_row_meta', 'da_widgets_admin_meta',10,2);

if (!defined('DA_WIDGETS_ADMIN_PAGE'))
	define('DA_WIDGETS_ADMIN_PAGE', implode(DIRECTORY_SEPARATOR, array_slice(explode(DIRECTORY_SEPARATOR, __FILE__), -3)));

function da_widgets_action_links($links, $file) {

	if('da-widgets/da-widgets.php' == $file && function_exists("admin_url")) {
		$settings_link = '<a href="' . admin_url('plugins.php?page=' . DA_WIDGETS_ADMIN_PAGE) . '">' . __('Settings', 'da-widgets') . '</a>';
		array_unshift($links, $settings_link); // before other links
	}

	return $links;
}

function da_widgets_admin_meta($links, $file) {
	if('da-widgets/da-widgets.php' == $file) {
		$links[] = '<a href="http://github.com/aegypius/wp-da-widgets/issues" target="_blank">' . __('Support', 'da-widgets') . '</a>';
	}
	return $links;
}

function da_widgets_admin_head() {
	if (isset($_GET['page']) && $_GET['page'] == DA_WIDGETS_ADMIN_PAGE) {?>
		<link rel="stylesheet" type="text/css" href="<?php echo compat_get_base_plugin_url()?>/<?php echo dirname(DA_WIDGETS_ADMIN_PAGE)?>/theme.css" />
		<script type="text/javascript"          src="<?php echo compat_get_base_plugin_url()?>/<?php echo dirname(DA_WIDGETS_ADMIN_PAGE)?>/admin.js"></script>
	<?php
	}
}

function da_widgets_admin_menu() {
	add_submenu_page(
		'plugins.php',								// parent
		__( 'deviantArt Widgets', 'da-widgets' ),	// page_title
		__( 'deviantArt Widgets', 'da-widgets' ),	// menu_title
		8,											// capability required
		DA_WIDGETS_ADMIN_PAGE,						// file/handle
		'da_widgets_admin_page'						// function
	);
	add_action('admin_init', 'da_widgets_admin_settings');
}


function da_widgets_admin_settings() {
	// Register Cache Settings
	register_setting('da-widgets-settings', 'cache-enabled');
	register_setting('da-widgets-settings', 'cache-duration');

	// Register Thumbs Settings
	register_setting('da-widgets-settings', 'thumb-enabled', 'intval');
	register_setting('da-widgets-settings', 'thumb-size-x', 'intval');
	register_setting('da-widgets-settings', 'thumb-size-y', 'intval');
	register_setting('da-widgets-settings', 'thumb-format');

	// Advanced Settings
	register_setting('da-widgets-settings', 'debug-enabled', 'intval');

	// CSS Customization
	register_setting('da-widgets-settings', 'user-css', 'trim');

	// Adding Cache cleaning
	register_setting('da-widgets-settings', 'empty-cache', 'da_widgets_admin_clean_cache' );

}

function da_widgets_admin_clean_cache($input) {
	if (!empty($input)) {
		$dir = new DirectoryIterator(realpath(ABSPATH . 'wp-content/cache'));
		foreach ($dir as $item) {
			if ($item->isFile() && strpos($item->getFilename(), 'da-widgets-') !== false)
				unlink($item->getPathname());
		}
	}
}

// Checks setup requirement
function da_widgets_admin_check() {

	// Checking GD availability
	if (!function_exists('gd_info'))
		$failure |= 1;

	// Checking GZip availability
	if (!function_exists('gzopen'))
		$failure |= 2;

	// Checking cURL availability
	if (!function_exists('curl_init'))
		$failure |= 4;

	// Checking PHP version
	$v = str_replace('.', '', preg_replace('/([0-9\.]+)(.*)/', '$1', phpversion())) / 100;
	if ($v < 5.2 && $v >= 5.3)
		$failure |= 8;

	// Checking if cache is writeable
	if (!is_writeable(realpath(ABSPATH . 'wp-content/cache'))) {
		$failure |= 16;
	} else {
		$fp = fopen(realpath(ABSPATH . 'wp-content/cache') . DIRECTORY_SEPARATOR . 'da-widgets-write-test', 'w+');
		if (is_resource($fp) && fputs($fp, 'Writing is OK'))
			fclose($fp);
		else
			$failure |= 16;
	}

	// Checking SHA1 function
	if (!function_exists('sha1'))
		$failure |= 32;

	// Checking Safe Mode    (cf issue:#1)
	if (ini_get('safe_mode') == 'On')
		$failure |= 64;

	// Checking OpenBase Dir (cf issue:#1)
	if (ini_get('open_basedir') != '')
		$failure |= 128;

	// Checking SimpleXmlElement (cf issue:#1)
	if (!class_exists('SimpleXmlElement'))
		$failure |= 256;

	// Checking for a method to access remote files
	if (!ini_get('allow_url_fopen') && !function_exists('curl_init'))
		$failure |= 512;

	return $failure;
}

function da_widgets_admin_page() {
?>
<div id="da-widgets-settings" class="wrap">
	<div id="da-widgets-settings-icon" class="icon32"><br /></div>
	<h2><?php _e('deviantArt Widgets Settings', 'da-widgets') ?></h2>
	<p id="da-widgets-version"> version <?php echo DA_Widgets::VERSION ?></p>

	<ul>
		<li class="tab"><a href="#general"><?php _e('General', 'da-widgets')?></a></li>
		<li class="tab"><a href="#customization"><?php _e('Customization', 'da-widgets')?></a></li>
	</ul>

	<form action="options.php" method="post">
		<?php wp_nonce_field('update-options'); ?>
		<?php settings_fields('da-widgets-settings'); ?>

		<div id="general" class="tab">
			<fieldset>
				<legend><?php _e('Cache Settings', 'da-widgets') ?></legend>
				<p><?php _e('Defines whether you want to use cache or not.', 'da-widgets')?></p>
				<dl>
					<dt><label for="cache-enabled"><?php _e('Enable cache', 'da-widgets') ?></label></dt>
					<dd><input <?php echo get_option('cache-enabled') ? 'checked="checked"' : '' ?> type="checkbox" id="cache-enabled" name="cache-enabled" value="1"/></dd>
					<dt><label <?php echo !get_option('cache-enabled') ? 'class="disabled"' : '' ?> for="cache-duration"><?php _e('Cache duration', 'da-widgets') ?></label></dt>
					<dd>
						<select <?php echo !get_option('cache-enabled') ? 'disabled="disabled"' : '' ?> id="cache-duration" name="cache-duration">
							<option <?php selected(get_option('cache-duration'),   15)?> value="15">15 <?php _e('minutes', 'da-widgets')?> (default)</option>
							<option <?php selected(get_option('cache-duration'),   30)?> value="30">30 <?php _e('minutes', 'da-widgets')?></option>
							<option <?php selected(get_option('cache-duration'),   60)?> value="60">1 <?php _e('hour', 'da-widgets')?></option>
							<option <?php selected(get_option('cache-duration'),  120)?> value="120">2 <?php _e('hour', 'da-widgets')?></option>
							<option <?php selected(get_option('cache-duration'),  180)?> value="180">3 <?php _e('hour', 'da-widgets')?></option>
							<option <?php selected(get_option('cache-duration'), 1440)?> value="1440">1 <?php _e('day', 'da-widgets')?></option>
						</select>
					</dd>
				</dl>
			</fieldset>
			<fieldset>
				<legend><?php _e('Thumbnails', 'da-widgets') ?></legend>
				<p><?php _e('Defines thumbnails properties.', 'da-widgets')?></p>
				<dl>
					<dt><label for="thumb-enabled"><?php _e('Thumbnail generation', 'da-widgets') ?></label></dt>
					<dd><input <?php echo get_option('thumb-enabled') ? 'checked="checked"' : '' ?> type="checkbox" id="thumb-enabled" name="thumb-enabled" value="1"/></dd>

					<dt><label <?php echo !get_option('thumb-enabled') ? 'class="disabled"' : '' ?> for="thumb-size-x"><?php _e('Thumbnail size', 'da-widgets')?></label></dt>
					<dd>
						<input <?php echo !get_option('thumb-enabled') ? 'disabled="disabled"' : '' ?> id="thumb-size-x" name="thumb-size-x" type="text" size="3" maxlength="3" value="<?php echo get_option('thumb-size-x')?>" />
						x
						<input <?php echo !get_option('thumb-enabled') ? 'disabled="disabled"' : '' ?> id="thumb-size-y" name="thumb-size-y" type="text" size="3" maxlength="3" value="<?php echo get_option('thumb-size-y')?>" />
					</dd>

					<dt><label <?php echo !get_option('thumb-enabled') ? 'class="disabled"' : '' ?> for="thumb-format"><?php _e('Thumbnail format', 'da-widgets') ?></label></dt>
					<dd>
						<select <?php echo !get_option('thumb-enabled') ? 'disabled="disabled"' : '' ?> id="thumb-format" name="thumb-format">
							<option <?php selected(get_option('thumb-format'), IMG_PNG)?> value="<?php echo IMG_PNG?>">PNG (default)</option>
							<option <?php selected(get_option('thumb-format'), IMG_JPG)?> value="<?php echo IMG_JPG?>">JPEG</option>
							<option <?php selected(get_option('thumb-format'), IMG_GIF)?> value="<?php echo IMG_GIF?>">GIF</option>
						</select>
					</dd>
				</dl>
			</fieldset>
			<fieldset>
				<legend><?php _e('Advanced options', 'da-widgets') ?></legend>
				<p><?php _e('Defines options to advanced features.', 'da-widgets')?></p>
				<dl>
					<dt><label for="debug-enabled"><? _e('Enable debug log', 'da-widgets')?></label></dt>
					<dd><input <?php echo get_option('debug-enabled') ? 'checked="checked"' : '' ?> type="checkbox" id="debug-enabled" name="debug-enabled" value="1"/></dd>
				</dl>
			</fieldset>
			<input type="submit" value="<?php _e('Save', 'da-widgets')?>" class="button-primary" />
			<input class="button" type="submit" value="<?php _e('Empty cache', 'da-widgets')?>" name="empty-cache" />

<?php
	$error_message = array(
		  1  => sprintf(__('"%s" extension is required for this plugin', 'da-widgets'), 'GD'),
		  2  => sprintf(__('"%s" extension is required for this plugin', 'da-widgets'), 'zlib'),
		  4  => sprintf(__('"%s" extension is required for this plugin', 'da-widgets'), 'cURL'),
		  8  => sprintf(__('PHP %.1f is required for this plugin', 'da-widgets'), 5.2),
		 16  => sprintf(__('Wordpress cache directory must be writeable (%s)', 'da-widgets'), 'wp-content/cache'),
		 32  => sprintf(__('"%s" function is required for this plugin', 'da-widgets'), 'sha1'),
		 64  => sprintf(__('Some issues can occure in safe_mode', 'da-widgets')),
		128  => sprintf(__('Some issues can occure when open_basedir is set (%s)', 'da-widgets'), ini_get('open_basedir')),
		256  => sprintf(__('"%s" extension is required for this plugin', 'da-widgets'), 'SimpleXml'),
		512  => sprintf(__('No suitable method to process remote images for this plugin (allow_url_fopen is disabled, curl is required)', 'da-widgets'))
	);
?>
			<fieldset>
				<legend><?php _e('Setup checks', 'da-widgets')?></legend>
				<ul>
<?php

	if (($failures = da_widgets_admin_check()) > 0) {
		for( $i = 1; $i <= $failures; $i = $i *2) {
			if (($failures & $i) != 0) {
				printf('<li class="message error">%s.</li>', preg_replace('/("([^"]+)")/', '<q>$2</q>', $error_message[$i]));
			}
		}
	} else {
		printf('<li class="message">%s.</li>', __('Everything is fine', 'da-widgets'));
	}
?>
				</ul>
			</fieldset>
		</div>

		<div id="customization" class="tab">
			<fieldset>
				<legend><?php _e('Style customization', 'da-widgets')?></legend>
				<dl class="horizontal">
					<dt><label for="user-css"><?php _e('Custom Styles', 'da-widgets')?></label></dt>
					<dd><textarea id="user-css" class="source" rows="10" cols="72" name="user-css"><?php echo get_option('user-css')?></textarea></dd>
				</dl>

				<p>
					<ul>
						<li><? _e('Galleries', 'da-widgets')?> : 
				<pre>
&lt;ul class="da-widgets gallery"&gt;
	&lt;li&gt;&lt;a [...]&gt;&lt;img [...]&gt;&lt;/a&gt;&lt;/li&gt;
	&lt;li&gt;&lt;a [...]&gt;&lt;img [...]&gt;&lt;/a&gt;&lt;/li&gt;
&lt;/ul&gt;</pre>
						</li>
						<li><? _e('Favourites', 'da-widgets')?> : 
				<pre>
&lt;ul class="da-widgets favourite"&gt;
	&lt;li&gt;&lt;a [...]&gt;&lt;img [...]&gt;&lt;/a&gt;&lt;/li&gt;
	&lt;li&gt;&lt;a [...]&gt;&lt;img [...]&gt;&lt;/a&gt;&lt;/li&gt;
&lt;/ul&gt;</pre>
						</li>
						<li><? _e('Logs', 'da-widgets')?> : 
				<pre>
&lt;dl class="da-widgets log"&gt;
	&lt;dt&gt;&lt;a [...]&gt;{title}&lt;/a&gt;&lt;/dt&gt;
	&lt;dd&gt;&lt;p&gt;{content}&lt;p&gt;&lt;/dd&gt;
&lt;/dl&gt;</pre>
						</li>
					</ul>
				</p>
				<input type="submit" value="<?php _e('Save', 'da-widgets')?>" class="button-primary" />
			</fieldset>
		</div>

	</form>
</div>
<?
}
