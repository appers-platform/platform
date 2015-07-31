<?
class bg {
	const ALIVE_KEY = '___bgQueues';

	static public function run($callback, array $arguments = []) {
		$queue = self::encodeCallback($callback);
		self::setAlive($queue);
		self::checkManager();
		background::instance()->run($queue, $arguments);
	}

	static public function encodeCallback($callback) {
		if($callback instanceof Closure) {
			$serializer = new SuperClosure\Serializer();
			$serialized = $serializer->serialize($callback);
			$key = md5($serialized);
			mCache::set('___bgCallback'.$key, $serialized);
			return '^^'.$key;
		}

		if(!is_callable($callback))
			throw new Exception('Invalid callback - is not callable');

		if(is_string($callback))
			return str_replace('\\', '^', $callback);
		if(is_array($callback) && is_string($callback[0]) && is_string($callback[1])) {
			return str_replace('\\', '^', $callback[0].'::'.$callback[1]);
		}
		throw new Exception('Invalid callback - only static calls allowed');
	}

	static public function decodeCallback($queue) {
		if(substr($queue, 0, 2) == '^^') {
			$key = substr($queue, 2);
			$serialized = mCache::get('___bgCallback'.$key);
			if(!$serialized) {
				throw new Exception('Can\'t get serialized callback');
			}
			$serializer = new SuperClosure\Serializer();
			return $serializer->unserialize($serialized);
		}

		$queue = str_replace('^', '\\', $queue);
		$queue = explode('::', $queue);
		if(count($queue) == 1)
			return $queue[0];
		if(count($queue) == 2)
			return [$queue[0], $queue[1]];
		throw new Exception('Invalid queue');
	}

	static protected function checkManager() {
		if(!PROJECT)
			throw new Exception("Can't run without of project");

		if(mCache::get($k = '___bgManagerChecked'))
			return;
		mCache::set($k, true, 5);

		$pid_file = ROOT.'/cache/'.PROJECT.'-sys_bg.pid';

		if(!is_file($pid_file) || !cli::checkPid(file_get_contents($pid_file))) {
			cli::runBackgroundTask('sys::bg', []);
		}
	}

	static public function setAlive($queue) {
		$queues = mCache::get(self::ALIVE_KEY);
		if(!is_array($queues))
			$queues = [];
		$queues[$queue] = time();
		mCache::set(self::ALIVE_KEY, $queues);
	}

	static public function checkAlive($queue) {
		$queues = mCache::get(self::ALIVE_KEY);
		if(!is_array($queues))
			$queues = [];
		$ttl = config::get('bg')['queue_ttl'] ?: TTL_5_MIN;

		if( (!isset($queues[$queue])) || ($queues[$queue] < (time() - $ttl)) ) {
			$result = false;
			unset($queues[$queue]);
		} else {
			$result = true;
		}

		mCache::set(self::ALIVE_KEY, $queues);

		return $result;
	}

	static public function getAll() {
		$queues = mCache::get(self::ALIVE_KEY);
		if(!is_array($queues))
			$queues = [];
		return array_keys($queues);
	}

	static public function restartWorkers() {
		return cli::runBackgroundTask('sys::bgRestart', []);
	}
}
