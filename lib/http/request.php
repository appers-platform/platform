<?
class request {
	static public function isGET() {
		return $_SERVER['REQUEST_METHOD'] == 'GET';
	}

	static public function isPOST() {
		return $_SERVER['REQUEST_METHOD'] == 'POST';
	}

	public static function isAJAX() {
		return (bool) $_SERVER['HTTP_X_REQUESTED_WITH'];
	}

	public static function isCLI() {
		return (php_sapi_name() === 'cli');
	}

	static public function get($name, $default = null) {
		return isset($_REQUEST[$name]) ? $_REQUEST[$name] : $default;
	}

	static public function getInt($name, $default = null) {
		return isset($_REQUEST[$name]) ? (int) $_REQUEST[$name] : $default;
	}

	static public function getFloat($name, $default = null) {
		return isset($_REQUEST[$name]) ? (float) $_REQUEST[$name] : $default;
	}

	public static function getMethod() {
		return $_SERVER['REQUEST_METHOD'];
	}

	public static function getUri() {
		return $_SERVER['REQUEST_URI'];
	}

	public static function getPath() {
		return explode('?', self::getUri())[0];
	}

	public static function getUrl() {
		return self::getProtocol().'://'.self::getHost().self::getUri();
	}

	public static function getHost() {
		return PROJECT;
	}

	public static function getProtocol() {
		return isset($_SERVER["https"]) ? 'https' : 'http';
	}

	public static function isFrame() {
		return (bool) self::getInt('_frame');
	}

	public static function getClientIP() {
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			return $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			return $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			return $_SERVER['REMOTE_ADDR'];
		}
	}

	public static function getAll($only_get_and_post = false) {
		return $only_get_and_post ? array_merge($_GET, $_POST) : $_REQUEST;
	}

	public static function getPOST() {
		return $_POST;
	}

	public static function getGET() {
		return $_GET;
	}

	public static function getReferer() {
		return $_SERVER['HTTP_REFERER'];
	}

	public static function isCurrentUri($uri, $strict = false) {
		$check = parse_url($uri);
		$check_path = $check['path'];

		$current = parse_url(self::getUri());
		$current_path = $current['path'];

		if($current_path != $check_path)
			return false;

		$check_query = $check['query'];
		$current_query = $current['query'];
		
		parse_str($check_query, $check_query);
		parse_str($current_query, $current_query);

		return helper::compareArrays($check_query, $current_query, !$strict);
	}
}
