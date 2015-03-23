<?
class mCache extends \system\cache {
	const DRIVER = '\Memcache';

	private $prefix;

	protected function __construct($instance) {
		$config = config::get('memcache');
		if(!isset($config[0]))
			$config = [$config];
		foreach($config as $server) {
			$this->prefix = $server['prefix'];
			$instance->addServer(
				$server['host'],
				$server['port'],
				(isset($server['persistent'])) ? $server['persistent'] : false, // Persistent. PHP default TRUE.
				(isset($server['weight'])) ? $server['weight'] : 1, // Weight. PHP default 1.
				(isset($server['timeout'])) ? $server['timeout'] : null, // Timeout. PHP default 1.
				(isset($server['retry_interval'])) ? $server['retry_interval'] : null, // Retry interval. PHP default 15.
				(isset($server['status'])) ? $server['status'] : true // Status. PHP default TRUE.
			);
		}
	}

	protected function getPrefix() {
		return $this->prefix;
	}
}