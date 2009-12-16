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
	}
}

function da_widgets_admin_menu() {
	add_submenu_page(
		'options-general.php',						// parent
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

	<div class="metabox-holder">
		<div class="postbox">
			<h3><span class="global-settings"><?php echo __( 'General Settings') ?></span></h3>
			<p>Cache</p>
			<p>Thumbnails</p>
		</div>
	</div>
</div>
<?
}


?>