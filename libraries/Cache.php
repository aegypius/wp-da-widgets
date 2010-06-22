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

	public function end($callback = null) {
		if (is_writeable(dirname($this->fragment))) {
			$src = ob_get_clean();
			$buffer = (!is_null($callback) && is_callable($callback) ? call_user_func_array($callback, array($src)) : $src);
			$fp = gzopen($this->fragment, 'w9');
			gzwrite($fp, $buffer, strlen($buffer));
			gzclose($fp);
		}
		echo $src;
	}

}



?>