<?php
require_once realpath(dirname(__FILE__) . '/..') . '/Feed.php';

class DeviantArt_Gallery extends Feed {

	const BACKEND_URL = 'http://backend.deviantart.com/rss.xml?q=gallery%%3A%s';
	protected $rating;

	public function DeviantArt_Gallery($username, $rating = null) {
		$url = sprintf(self::BACKEND_URL, $username);
		$this->rating = $rating;
		parent::Feed($url);
	}

	public function get($count = -1) {
		$xml = new SimpleXmlElement($this->data);
		$ns = $xml->getNamespaces(true);

		$items = null;
		foreach ($xml->channel->item as $item) {

			$media = $item->children($ns['media']);

			if (!(is_null($this->rating) || $this->rating == 'all') && $media->rating != $this->rating)
				continue;

			if ($media->text)
				continue;

			$items .= sprintf(
				'<li><a href="%1$s" title="%2$s - %3$s"><img src="%4$s" alt="%2$s - %3$s"/></a></li>'
				, $item->link
				, $media->title
				, $media->copyright
				, $media->content->attributes()->url
			);

			--$count;
			if ($count > -1 && $count == 0)
				break;
		}

		return sprintf('<ul class="da-widgets favourite">%s</ul>', $items);
	}

	public function __toString() {
		return $this->get();
	}
}

?>