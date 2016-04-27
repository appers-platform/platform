<?
namespace system;

use kvStorage;
use timer;
use Exception;

abstract class cache implements kvStorage {
	protected static $_instance = null;
	protected static $timer = null;
	protected static $_prefix = null;
	protected static $local_cache = true;
	protected static $local = [];

	abstract protected function __construct($instance);
	static function getDriver() {
		throw new Exception("Should be redeclarated");
	}

	protected function getPrefix() {
		return '';
	}

	/**
	 * @return timer
	 */
	static protected function getTimer() {
		if(!static::$timer)
			static::$timer = new timer(false);
		return static::$timer;
	}

	static protected function instance() {
		if(get_called_class() == __CLASS__)
			throw new Exception('Direct call of method is denied.');

		if(!static::$_instance) {
			$driver = static::getDriver();
			if(!$driver)
				throw new Exception('Driver is not set');
			static::$_instance = new $driver();
			$obj = new static(static::$_instance);
			static::$_prefix = $obj->getPrefix();
			if ( PHP_SAPI == 'cli' ) {
				self::$local_cache = false;
			}
		}

		return static::$_instance;
	}

	static public function get($name) {
		if(get_called_class() == __CLASS__)
			throw new Exception('Direct call of method is denied.');

		if (is_null($name)) return null;
		if ( static::$local_cache && array_key_exists($name, static::$local) && (static::$local[$name] !== false) )
			return static::$local[$name];

		static::getTimer()->start();
		$result = static::instance()->get(static::$_prefix.\solutions::getStoragePrefix().$name);
		static::getTimer()->stop();

		return $result;
	}

	static public function set($name, $value, $ttl = 0) {
		if(get_called_class() == __CLASS__)
			throw new Exception('Direct call of method is denied.');
		if (is_null($name)) return null;
		if ( static::$local_cache ) static::$local[$name] = false;
		static::getTimer()->start();
		$result = static::instance()->set(static::$_prefix.\solutions::getStoragePrefix().$name, $value, $ttl);
		static::getTimer()->stop();

		return $result;
	}

	static public function inc($name, $value = 1) {
		if(get_called_class() == __CLASS__)
			throw new Exception('Direct call of method is denied.');
		if (is_null($name)) return null;
		if ( static::$local_cache ) static::$local[$name] = false;
		static::getTimer()->start();
		$result = static::instance()->increment(static::$_prefix.\solutions::getStoragePrefix().$name, $value);
		static::getTimer()->stop();

		return $result;
	}

	static public function dec($name, $value = 1) {
		if(get_called_class() == __CLASS__)
			throw new Exception('Direct call of method is denied.');
		if (is_null($name)) return null;
		if ( static::$local_cache ) static::$local[$name] = false;
		static::getTimer()->start();
		$result = static::instance()->decrement(static::$_prefix.\solutions::getStoragePrefix().$name);
		static::getTimer()->stop();

		return $result;
	}

	static public function delete($name) {
		if(get_called_class() == __CLASS__)
			throw new Exception('Direct call of method is denied.');
		if (is_null($name)) return null;
		if ( static::$local_cache ) static::$local[$name] = false;
		static::getTimer()->start();
		$result = static::instance()->delete(static::$_prefix.\solutions::getStoragePrefix().$name);
		static::getTimer()->stop();

		return $result;
	}
}