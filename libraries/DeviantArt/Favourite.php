<?php
require_once realpath(dirname(__FILE__) . '/..') . '/Feed.php';

class DeviantArt_Favourite extends Feed {

	const BACKEND_URL = 'http://backend.deviantart.com/rss.xml?q=favby%%3A%s';
	protected $username;
	protected $rating;

	public function DeviantArt_Favourite($username, $rating = null) {
		$this->username = trim($username);
		$this->rating = $rating;
		parent::Feed();
	}

	public function get($count = -1, $rating = null, $filter = null) {
		if ($count == 0 || !is_numeric($count)) $count = -1;

		$url = sprintf(self::BACKEND_URL, $this->username)
		     . ($filter == -1 ? '+in%3Ascraps'  : '')
		     . ($filter  >  0 ? '%2F' . $filter : '');

		$this->data = $this->request($url);

		$xml = new SimpleXmlElement($this->data);
		$ns = $xml->getNamespaces(true);

		$items = null;
		foreach ($xml->channel->item as $item) {

			$media = $item->children($ns['media']);

			if (!(empty($this->rating) || $this->rating == 'all') && $media->rating != $this->rating)
				continue;

			if ($media->text)
				continue;

			if ($media->text)
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

	public function getCategories() {

		$url = sprintf('http://%s.deviantart.com/favourites/', $this->username);
		$content = $this->request($url, "Mozilla/5.0 (Android;)");

		$cats = array();
		if (preg_match_all(';\s+href="http://[^\.]+\.deviantart\.com/\?tab=faves&id=([^&]+)[^"]+".*<h3>([^<]+);', $content, $m)) {
			$cats = array_combine($m[1], $m[2]);
		}
		return $cats;
	}

	public function __toString() {
		return $this->get();
	}
}

?>