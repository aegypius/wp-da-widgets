<?php
require_once realpath(dirname(__FILE__) . '/..') . '/Feed.php';

class DeviantArt_Favourite extends Feed {

	const BACKEND_URL = 'http://backend.deviantart.com/rss.xml?q=favby%3A%s';
	protected $feed;

	public function DeviantArt_Favourite($username) {
		parent::Feed(sprintf(self::BACKEND_URL, $username));
	}
}

?>