<?php
require_once '../libraries/Image.php';
defined('ABSPATH') or define('ABSPATH', realpath(dirname(__FILE__) . '/../../../..'));

/*
	Generates Thumbnail and redirect
*/

$picture = filter_var($_GET['u'], FILTER_SANITIZE_URL);
$width   = intval($_GET['w']);
$height  = intval($_GET['h']);
$format  = intval($_GET['f']);

if (empty($picture) || !$width || !$height || !$format) {
	header('HTTP/1.1 404 Not Found');
	return;
}

switch ($format) {
	case IMG_PNG: $ext = 'png'; break;
	case IMG_GIF: $ext = 'gif'; break;
	case IMG_JPG: $ext = 'jpg'; break;
}

$thumbfile = '/wp-content/cache' . DIRECTORY_SEPARATOR . 'da-widgets-' . sha1($picture . $width . $height) . '.' . $ext;
// TODO : Update this old image library
if (!file_exists(ABSPATH . $thumbfile)) {
	$thumb = Image::CreateFromFile($picture);
	Image::Resize($thumb, $width * 2, $height == null ? null : $height * 2);

	Image::Crop($thumb, $width, $height, false, false, IMAGE_ALIGN_CENTER | IMAGE_ALIGN_CENTER);

	if (is_writeable(dirname(ABSPATH . $thumbfile))) {
		Image::Output($thumb, IMAGE_OUTPUTMODE_FILE, $format, ABSPATH . $thumbfile);
	} else {
		return;
	}
}

header('HTTP/1.1 301 Moved Permanently');
header('Location: '.$thumbfile);