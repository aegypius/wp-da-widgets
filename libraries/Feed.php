<?php

class Feed {

	protected $encoding = 'utf-8';
	protected $url;

	public function Feed($url = null) {
		if (is_string($url) && strlen($url)) {
			$this->url = trim($url);
		}
	}

	protected function request($url = null, $ua = null) {

		// curl initialization
		$ch = curl_init();

		if (empty($url))
			$url = $this->url;

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_TIMEOUT, 3);

		if (!empty($ua))
			curl_setopt($ch, CURLOPT_USERAGENT, $ua);

		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		if (ini_get('open_basedir') == '' && ini_get('safe_mode' == 'Off'))
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

		$content = curl_exec($ch);

		if (curl_errno($ch) != CURLE_OK) {
			$errno = curl_errno($ch);
			$error = curl_error($ch);
			curl_close($ch);
		}

		return $content;
	}

}

?>
