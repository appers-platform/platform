<?
class cookie implements kvStorage {
	static private $cache = [];
	static private $inited = false;

	static public function set($name, $value, $ttl = false) {
		$name = \solutions::getStoragePrefix().$name;
		response::sendP3P();
		setcookie($name, $value, $ttl ? time() + $ttl : null, '/');
		$_COOKIE[$name] = $value;
	}

	static public function get($name) {
		$name = \solutions::getStoragePrefix().$name;
		return $_COOKIE[$name];
	}

	static public function delete($key) {
		self::set($key, null, -1);
		unset($_COOKIE[$key]);
	}

	static public function inc($key, $value = 1) {
		self::set($key, $value + (int) self::get($key));
	}

	static public function dec($key, $value = 1) {
		self::set($key, $value - (int) self::get($key));
	}

	static public function getAll() {
		return $_COOKIE;
	}
}