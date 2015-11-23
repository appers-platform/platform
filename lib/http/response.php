<?
class response {
	protected static $headers = [];
	protected static $p3p_sent = false;
	protected static $sent = false;

	static public function redirect($url, $status = 302) {
		header('Location: '.$url, true, $status);
		self::send();
		exit;
	}

	public static function redirectTop( $url, $status = 302 ) {
		$content = '';
		if ( isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) ) {
			self::send_header($url, 'X-REDIRECT');
		} else {
			if(\request::isFrame()) {
				$content = '<html><head><script>top.location.href="'.str_replace('\'', '\\\'', $url).'";</script></head></html>';
			} else {
				header('Location: '.$url, true, $status);
			}
		}
		self::send($content);
		exit;
	}

	static public function refresh($params = []) {
		$get_params = '';
		if (!empty($params)) {
			foreach($params as $key => $value) {
				$get_params .= $key.'='.$value;
			}			
		}
		
		header('Location: ' . request::getUri() . '?' . $get_params, true, 302);
	}	

	static public function setHeader($name, $value) {
		self::$headers[$name] = $value;
	}

	static public function isSent() {
		return self::$sent;
	}

	static public function send($content = '') {
		if(self::$sent) {
			throw new Exception("Already sent");
		}

		self::$sent = true;

		foreach(self::$headers as $header => $value) {
			if($header == 'Content-Type') {
				header($header.': '.$value);
			} else {
				header($header, $value);
			}
		}

		print $content;
	}

	static public function sendP3P() {
		if(self::$p3p_sent)
			return;
		header('P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');
		self::$p3p_sent = true;
	}
}