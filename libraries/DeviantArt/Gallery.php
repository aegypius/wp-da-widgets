<?php
require_once realpath(dirname(__FILE__) . '/..') . '/Feed.php';

class DeviantArt_Gallery extends Feed {

	const BACKEND_URL = 'http://backend.deviantart.com/rss.xml?q=by%3A%s';
	protected $feed;

	public function DeviantArt_Gallery($username) {
		parent::Feed(sprintf(self::BACKEND_URL, $username));
	}
}

?>