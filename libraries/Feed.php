<?php

class Feed {

	protected $encoding = 'utf-8';
	protected $url;
	protected $data;

	public function Feed($url = null) {
		if (is_string($url) && strlen($url)) {
			$this->url = trim($url);
			$this->request();
		}
	}

	protected function request() {

		// curl initialization
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $this->url);
		curl_setopt($ch, CURLOPT_TIMEOUT, 3);

		curl_setopt($ch, CURLOPT_HTTPHEADERS,array('Content-Type: text/xml'));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		if (ini_get('open_basedir') == '' && ini_get('safe_mode' == 'Off'))
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

		$content = curl_exec($ch);

		if (curl_errno($ch) != CURLE_OK) {
			$errno = curl_errno($ch);
			$error = curl_error($ch);
			curl_close($ch);
		}

		return $this->data = $content;
	}

}

?>
