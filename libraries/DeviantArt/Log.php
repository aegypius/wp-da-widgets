<?php
require_once realpath(dirname(__FILE__) . '/..') . '/Feed.php';

class DeviantArt_Log extends Feed {

	const BACKEND_URL = 'http://backend.deviantart.com/rss.xml?q=by%3A%s&type=journal&formatted=1';
	protected $feed;

	public function DeviantArt_Log($username) {
		parent::Feed(sprintf(self::BACKEND_URL, $username));
	}
}

?>