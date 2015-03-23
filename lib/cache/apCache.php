<?
class apCacheDriver {
	public function get($key) {
		return apc_fetch($key);
	}

	public function set($key, $value, $ttl = 0) {
		return apc_store($key, $value, $ttl);
	}

	public function inc($key, $value = 1) {
		$this->set($key, $result = ($this->get($key) + $value));
		return $result;
	}

	public function dec($key, $value = 1) {
		return $this->inc($key, $value * -1);
	}

	public function delete($key) {
		return apc_delete($key);
	}
}

class apCache extends \system\cache {
	const DRIVER = '\apCacheDriver';

	private $prefix;

	protected function __construct($instance) {
		$config = config::get('apc');
		if($config['prefix']) {
			$this->prefix = $config['prefix'];
		}
	}

	protected function getPrefix() {
		return $this->prefix;
	}
}