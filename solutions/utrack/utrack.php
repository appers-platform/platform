<?
namespace solutions;

use Exception;
use mCache;
use request;

class utrack extends solution {
	static protected $id = null;

/*
	static public function init() {
		if(parent::init()) {
			\event::addCallback('afterControllerRender', [__CLASS__, '_trackRender']);
		}
	}

	static public function _trackRender() {
		$id = self::getId();
		// ???

	}
*/

	static protected function getIdFromCookie() {
		$id = \cookie::get('id');

		if( dechex(hexdec($id)) == $id ) {
			return $id;
		}

		return 0;
	}

	static protected function setIdToCookie() {
		\cookie::set('id', self::$id);
	}

	static protected function generateId() {
		return uniqid().dechex(rand(256, 1024));
	}

	static public function getId() {
		if(!self::$id) self::$id = self::getIdFromCookie();
		if(!self::$id) {
			self::$id = self::generateId();
			self::setIdToCookie();
		}
	}

	static protected function getDriverClass() {
		$driver_class = static::getConfig('driver');

		if(!class_exists($driver_class))
			throw new Exception('Invalid driver');

		if(!((new $driver_class) instanceof \solutions\utrack\storageDriver))
			throw new Exception('Invalid driver');
		
		return $driver_class;
	}

	static protected function push($table, $data, $sync = false) {
		$driver_class = self::getDriverClass();

		self::installTables($driver_class);

		if($sync)
			return $driver_class::syncPush($table, $data);

		return $driver_class::push($table, $data);
	}

	static protected function getNow() {
		$driver_class = self::getDriverClass();
		return $driver_class::getNow();
	}

	static public function addData($key, $value, $source, $user_id = null) {
		if(!$user_id && request::isCLI())
			throw new Exception('User ID is required');

		if(!$user_id) {
			$user_id = self::getId();
		}

		self::push('events', [
			'user' => $user_id,
			'name' => $name,
			'value' => $value,
			'source' => $source,
			'ts' => self::getNow(),
		]);
	}

	static public function trackEvent($name, $value = null, $user_id = null) {
		if(!$user_id && request::isCLI())
			throw new Exception('User ID is required');

		if(!$user_id) {
			$user_id = self::getId();
		}

		self::push('events', [
			'user' => $user_id,
			'event' => $name,
			'value' => $value,
			'ts' => self::getNow(),
		]);
	}

	static protected function installTables($driver_class) {
		if(mCache::get($k = 'tables_installed'))
			return;
		mCache::set($k, true);
		$sql = file_get_contents(dirname(__FILE__).'/data/'.str_replace('\\', '_', $driver_class).'.sql');
		$driver_class = self::getDriverClass();
		$driver_class::query($sql);
	}
}

