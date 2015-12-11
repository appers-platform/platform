<?
namespace solutions;

use Exception;
use mCache;
use request;
use session;
use bg;
use log;

class utrack extends solution {
	static protected $id = null;
	static protected $page_tracked = false;


	static public function init() {
		if(parent::init()) {
			\event::addCallback('afterControllerRender', [__CLASS__, '_trackRender']);
		}
	}

	static public function _trackRender() {
		if(static::getConfig('tackPages', false)) {
			self::trackPageview();
		}
	}

	static public function trackPageview($page = null, $content = null) {
		if(self::$page_tracked)
			return ;
		self::$page_tracked = true;
		if(!$page)
			$page = request::getUri();

		self::trackEvent('pageview', [
			'url'		=> $page,
			'content'	=> $content,
			'referal'	=> request::getReferer(),
			'session'	=> session::getid()
		]);
	}

	static protected function getIdFromCookie() {
		$id = \cookie::get('id');

		if( dechex(hexdec($id)) == $id ) {
			return $id;
		}

		return 0;
	}

	static protected function setIdToCookie() {
		\cookie::set('id', self::$id, 3*TTL_YEAR);
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

		return self::$id;
	}

	static public function getDriverClass() {
		$driver_class = static::getConfig('driver');

		if(!class_exists($driver_class))
			throw new Exception('Invalid driver');

		if(!((new $driver_class) instanceof \solutions\utrack\storageDriver))
			throw new Exception('Invalid driver');
		
		return $driver_class;
	}

	static public function push($table, $data, $sync = false) {
		log::debug(__METHOD__.':'.print_r(func_get_args(), true));

		$driver_class = self::getDriverClass();
		self::installTables($driver_class);

		foreach($data as $k => $v) {
			if(in_array($k, \solutions\utrack\cases::getTypes())) {
				unset($data[$k]);
				$data[$k.'_id'] = \solutions\utrack\cases::getId($k, $v);
			}
		}

		return $driver_class::syncPush('solutions_utrack_'.$table, $data);
	}

	static protected function getNow() {
		$driver_class = self::getDriverClass();
		return $driver_class::getNow();
	}

	static public function addData($name, $value, $source = '', $user_id = null) {
		if(!$user_id && request::isCLI())
			throw new Exception('User ID is required');

		if(!$user_id) {
			$user_id = self::getId();
		}

		if(!is_string($value) && !is_numeric($value)) {
			$value = json_encode($value);
		}

		$driver_class = self::getDriverClass();
		bg::run([__CLASS__, 'push'], [
			'data', [
				'user' => $user_id,
				'data_name' => $name,
				'data_value' => $value,
				'data_source' => $source,
				'ts' => $driver_class::getNow(),
			]
		]);
	}

	static public function trackEvent($name, $value = null, $user_id = null) {
		log::debug(__METHOD__.':'.print_r(func_get_args(), true));

		if(!is_string($value) && !is_numeric($value)) {
			$value = json_encode($value);
		}

		if(!$user_id && request::isCLI())
			throw new Exception('User ID is required');

		if(!$user_id) {
			$user_id = self::getId();
		}

		$driver_class = self::getDriverClass();
		bg::run([__CLASS__, 'push'], [
			'events', [
				'user' => $user_id,
				'event_name' => $name,
				'event_value' => $value,
				'ts' => $driver_class::getNow(),
			]
		]);
	}

	static protected function installTables($driver_class) {
		log::debug(__METHOD__.':1');

		$driver_class = self::getDriverClass();
		if(mCache::get($k = $driver_class.'?tables_installed'))
			return;
		mCache::set($k, true);

		log::debug(__METHOD__.':2');

		$sql = file_get_contents(dirname(__FILE__).'/data/'.str_replace('\\', '_', $driver_class).'.sql');
		
		$driver_class::query($sql);
	}
}

