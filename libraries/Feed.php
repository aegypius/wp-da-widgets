<?php

class Feed {

	protected $encoding = 'utf-8';
	protected $url = null;

	public function Feed($url = null) {
		if (is_string($url) && strlen($url))
			$this->url = trim($url);
	}

}

?>