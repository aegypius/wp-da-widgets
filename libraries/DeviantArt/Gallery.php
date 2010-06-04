<?php
require_once realpath(dirname(__FILE__) . '/..') . '/Feed.php';

class DeviantArt_Gallery extends Feed {

	const BACKEND_URL = 'http://backend.deviantart.com/rss.xml?q=gallery%%3A%s';
	protected $rating;
	protected $username;

	public function DeviantArt_Gallery($username) {
		$this->username = trim($username);
		$this->rating = $rating;
		parent::Feed();
	}

	public function get($count = -1, $rating = null, $filter = null, $return = 'html') {
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

			if ($return == 'html') {
				$items .= sprintf(
					'<li><a href="%1$s" title="%2$s - %3$s"><img src="%4$s" alt="%2$s - %3$s"/></a></li>'
					, $item->link
					, $media->title
					, $media->copyright
					, $media->content->attributes()->url
				);
			} else {
				$o            = new StdClass;
				$o->link      = (string) $item->link;
				$o->title     = (string) $media->title;
				$o->copyright = (string) $media->copyright;
				$o->content   = (string) $media->content->attributes()->url;
				$o->author    = (string) $media->credit[0];
				$o->symbol    = (string) substr($media->copyright, strpos($media->copyright, $o->author) -1, 1);
				$o->avatar    = (string) $media->credit[1];
				$o->published = strtotime($item->pubDate);
				$items[]      = $o;
			}

			--$count;
			if ($count > -1 && $count == 0)
				break;
		}

		return ($return == 'html' ? sprintf('<ul class="da-widgets gallery">%s</ul>', $items) : $items);
	}

	public function getCategories() {

		$url = sprintf('http://%s.deviantart.com/gallery/', $this->username);
		$content = $this->request($url, "Mozilla/5.0 (Android;)");

		$cats = array();
		if (preg_match_all(';\s+href="http://[^\.]+\.deviantart\.com/\?tab=gallery&id=([^&]+)[^"]+".*<h3>([^<]+);', $content, $m)) {
			$cats = array_combine($m[1], $m[2]);
		}
		return $cats;
	}

	public function __toString() {
		return $this->get();
	}
}

?>