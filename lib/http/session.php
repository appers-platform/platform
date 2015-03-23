<?
class session extends singleton implements kvStorage {
	protected function __construct() {
		if ( PHP_SAPI == 'cli' )
			return;

		response::sendP3P();

		if($session_host = config::get('session_host'))
			session_set_cookie_params(0, '/', $session_host);

		if ($session_name = config::get('session_name'))
			session_name($session_name);

		session_start();
	}

	public function __get( $name ) {
		return $_SESSION[\solutions::getStoragePrefix().$name];
	}

	public function __set($name, $value) {
		return $_SESSION[\solutions::getStoragePrefix().$name] = $value;
	}

	private function _delete($name) {
		unset($_SESSION[\solutions::getStoragePrefix().$name]);
	}

	public static function destroy() {
		self::instance();
		session_destroy();
	}

	public static function clear() {
		self::instance();
		session_unset();
	}

	public static function getAll( ) {
		return self::instance()->_getAll();
	}

	private function _getAll() {
		return $_SESSION;
	}

	public static function get( $name ) {
		$expire = self::instance()->{'____________ttl_'.md5($name)};
		if($expire && $expire < time()) {
			self::delete( $name );
			return null;
		}
		return self::instance()->$name;
	}

	public static function set( $name, $value, $ttl = 0 ) {
		if($ttl) {
			self::instance()->{'____________ttl_'.md5($name)} = time() + $ttl;
		} else {
			self::instance()->_delete('____________ttl_'.md5($name));
		}
		return self::instance()->$name = $value;
	}

	public static function inc( $name, $value = 1 ) {
		self::set($name, $result = ((double) self::get($name)) + $value);
		return $result;
	}

	public static function dec( $name, $value = 1 ) {
		self::set($name, $result = ((double) self::get($name)) - $value);
		return $result;
	}

	public static function delete( $name ) {
		self::instance()->_delete('____________ttl_'.md5($name));
		self::instance()->_delete( $name );
	}

}
