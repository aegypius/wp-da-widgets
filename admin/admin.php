<?php

add_action('admin_head',			'da_widgets_admin_head');
add_action('admin_menu',			'da_widgets_admin_menu'); 
add_filter('plugin_action_links',	'da_widgets_action_links', 9, 2);

if (!defined('DA_WIDGETS_ADMIN_PAGE'))
	define('DA_WIDGETS_ADMIN_PAGE', implode(DIRECTORY_SEPARATOR, array_slice(explode(DIRECTORY_SEPARATOR, __FILE__), -3)));

function da_widgets_action_links($links, $file) {
	
	if('da-widgets/da-widgets.php' == $file && function_exists("admin_url")) {
		$settings_link = '<a href="' . admin_url('options-general.php?page=' . DA_WIDGETS_ADMIN_PAGE) . '">' . __('Settings') . '</a>';
		array_unshift($links, $settings_link); // before other links
	}
	return $links;
}

function da_widgets_admin_head() {
	if (isset($_GET['page']) && $_GET['page'] == DA_WIDGETS_ADMIN_PAGE) {
		echo "<link rel='stylesheet' type='text/css' href='" . compat_get_base_plugin_url() .'/'. dirname(DA_WIDGETS_ADMIN_PAGE) . "/theme.css' />\n";
		echo "<script type='text/javascript' src='" . compat_get_base_plugin_url() .'/'. dirname(DA_WIDGETS_ADMIN_PAGE) . "/admin.js' />\n";
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
}

function da_widgets_admin_page() {
?>
<div id="da-widgets-settings" class="wrap">
	<div id="da-widgets-settings-icon" class="icon32"><br /></div>
	<h2><?php echo __( 'deviantArt Widgets Settings', 'da-widgets' ) ?></h2>
	<p id="da-widgets-version"> version <?php echo DA_Widget::VERSION ?></p>

	<form action="" method="post">
		<fieldset>
			<legend><?php echo __( 'Cache Settings') ?></legend>
			<p><?php echo __('Defines whether you want to use cache or not.')?></p>
			<dl>
				<dt><label for="cache-enabled"><?php echo __('Enable cache') ?></label></dt>
				<dd><input type="checkbox" id="cache-enabled" name="cache-enabled" value="1"/></dd>
				<dt><label for="cache-path"><?php echo __('Cache directory') ?></label></dt>
				<dd><input type="text" id="cache-path" name="cache-path" value=""/></dd>
				<dt><label for="cache-duration"><?php echo __('Cache duration') ?></label></dt>
				<dd>
					<select id="cache-duration" name="cache-duration">
						<option id="15">15 <?php echo __('minutes')?> (default)</option>
						<option id="30">30 <?php echo __('minutes')?></option>
						<option id="60">1 <?php echo __('hour')?></option>
						<option id="120">2 <?php echo __('hour')?></option>
						<option id="180">3 <?php echo __('hour')?></option>
						<option id="1440">1 <?php echo __('day')?></option>
					</select>
				</dd>
			</dl>
		</fieldset>
		<fieldset>
			<legend><?php echo __( 'Thumbnails') ?></legend>
			<p><?php echo __('Defines thumbnails properties.')?></p>
			<dl>
				<dt><label for="thumb-enabled"><?php echo __('Thumbnail generation') ?></label></dt>
				<dd><input type="checkbox" id="thumb-enabled" name="thumb-enabled" value="1"/></dd>
				<dt><label for="thumb-size-x"><?php echo __('Thumbnail size')?></label></dt>
				<dd>
					<input id="thumb-size-x" name="thumb-size[x]" type="text" size="3" maxlength="3" value="" />
					x
					<input id="thumb-size-y" name="thumb-size[y]" type="text" size="3" maxlength="3" value="" />
				</dd>
			</dl>
		</fieldset>

		<input type="submit" value="<?php echo __('Save')?>" class="button-primary" />

	</form>
</div>
<?
}


?>