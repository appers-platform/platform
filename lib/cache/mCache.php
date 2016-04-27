<?
class mCache extends \system\cache {
	private $prefix;

	protected function __construct($instance) {
		$config = config::get('memcache');
		if(!isset($config[0]))
			$config = [$config];
		foreach($config as $server) {
			$this->prefix = $server['prefix'];

			if(self::getDriver() == '\Memcache') {
				$instance->addServer(
					$server['host'],
					$server['port'],
					(isset($server['persistent'])) ? $server['persistent'] : false, // Persistent. PHP default TRUE.
					(isset($server['weight'])) ? $server['weight'] : 1, // Weight. PHP default 1.
					(isset($server['timeout'])) ? $server['timeout'] : null, // Timeout. PHP default 1.
					(isset($server['retry_interval'])) ? $server['retry_interval'] : null, // Retry interval. PHP default 15.
					(isset($server['status'])) ? $server['status'] : true // Status. PHP default TRUE.
				);
			} else if (self::getDriver() == '\Memcached') {
				$instance->addServer(
					$server['host'],
					$server['port'],
					(isset($server['weight'])) ? $server['weight'] : 1 // Weight. PHP default 1.
				);
			} else throw new Exception("Unknown driver '".self::getDriver()."'");
			
		}
	}

	protected function getPrefix() {
		return $this->prefix;
	}

	static function getDriver() {
		if(loader::autoLoad('Memcache', false))
			return '\Memcache';
		if(loader::autoLoad('Memcached', false))
			return '\Memcached';
		throw new Exception('Memcache driver "Memcache" or "Memcached" not found');
	}
}