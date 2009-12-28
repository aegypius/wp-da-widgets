<?php


class Cache {
	protected $fragment = null;
	protected $ttl = '+15 minutes';

	public function __construct($fragment, $ttl = '+15 minutes') {
		$this->fragment	= $fragment;
		$this->ttl		= $ttl;
	}

	public function start() {
		if (!is_writeable(dirname($this->fragment)))
			return true;

		if ($this->ttl != -1 && file_exists($this->fragment) && (strtotime($this->ttl , filemtime($this->fragment)) >= time())) {
			$fp = gzopen($this->fragment, 'r');
			while (!feof($fp))
				echo gzread($fp, 1024);
			gzclose($fp);
			return false;
		}

		ob_start();
		return true;
	}

	public function end() {
		if (is_writeable(dirname($this->fragment))) {
			$src = ob_get_clean();
			$fp = gzopen($this->fragment, 'w9');
			gzwrite($fp, $src, strlen($src));
			gzclose($fp);
		}
		echo $src;
	}

}



?>