<?php
require_once realpath(dirname(__FILE__) . '/..') . '/Feed.php';

class DeviantArt_Log extends Feed {

	const BACKEND_URL = 'http://backend.deviantart.com/rss.xml?q=by%%3A%s&type=journal&formatted=%d';
	protected $username;

	public function DeviantArt_Log($username, $format=1) {
		$this->username = trim($username);
		parent::Feed();
	}

	public function get($count = -1, $format = 1) {
		if ($count == 0 || !is_numeric($count)) $count = -1;

		$url = sprintf(self::BACKEND_URL, $this->username, $format);

		$this->data = $this->request($url);

		$xml = new SimpleXmlElement($this->data);
		$ns = $xml->getNamespaces(true);

		$items = null;
		foreach ($xml->channel->item as $item) {
			$items .= sprintf(
				  '<dt><a href="%2$s">%1$s</a></dt>'
				. '<dd>'
					. '<p>%3$s</p>'
				. '</dd>'
				, $item->title
				, $item->link
				, $item->description
			);

			--$count;
			if ($count > -1 && $count == 0)
				break;

		}

		return sprintf('<dl class="da-widgets log">%s</dl>', $items);
	}

	public function __toString() {
		return $this->get();
	}
}

?>