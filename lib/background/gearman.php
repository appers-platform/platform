<?
namespace background;

use singleton;
use backgroundDriver;
use config;
use memoryLimitException;

class gearmanClient extends singleton {
	private $handle;

	protected function __construct(array $config) {
		$this->handle = new \GearmanClient();
		$this->handle->addServer( $config['host'], $config['port'] );
	}

	public function __call($method, $arguments) {
		return call_user_func_array([$this->handle, $method], $arguments);
	}

	public function __get($key) {
		return $this->handle->$key;
	}

	public function __set($key, $value) {
		$this->handle->$key = $value;
	}
}

class gearmanWorker extends singleton {
	private $handle;

	protected function __construct(array $config) {
		$this->handle = new \GearmanWorker();
		$this->handle->addOptions(GEARMAN_WORKER_NON_BLOCKING);
		$this->handle->setTimeout(2500);
		try {
			$this->handle->addServer( $config['host'], $config['port'] );
		} catch (GearmanException $e) {
			if($e->getMessage() == 'Failed to set exception option') {
				trigger_error("Can't connect to gearman [host: {$config['host']}, port: {$config['port']}]", E_USER_ERROR);
			}
			throw $e;
		}
	}

	public function __call($method, $arguments) {
		return call_user_func_array([$this->handle, $method], $arguments);
	}

	public function __get($key) {
		return $this->handle->$key;
	}

	public function __set($key, $value) {
		$this->handle->$key = $value;
	}
}

class gearman extends singleton implements backgroundDriver {
	private $config;
	private $terminated = false;
	static public $last_memory_checked = 0;

	protected function __construct($config = '') {
		$this->config = config::get('gearman'.( $config ? ('_'.$config) : '' ));
		if(!$this->config) {
			throw new \Exception('Config is not valid.');
		}
	}

	protected function getServerTaskName($task) {
		return $this->config['prefix'] ? $this->config['prefix'].'_'.$task : $task;
	}

	public function addTask($task, $data) {
		$task = $this->getServerTaskName($task);
		$try = 0;
		$retry = $this->config['retry'] ?: 0;
		do {
			$result = @gearmanClient::instance($this->config)->doBackground( $task, serialize($data) );
		} while ($result != GEARMAN_SUCCESS && $try++ < $retry );

		if($result != GEARMAN_SUCCESS) {
			throw new \Exception(
				gearmanClient::instance($this->config)->error(),
				gearmanClient::instance($this->config)->getErrno()
			);
		}

		return $result;
	}

	public function addTaskLow($task, $data) {
		$task = $this->getServerTaskName($task);
		$try = 0;
		$retry = $this->config['retry'] ?: 0;
		do {
			$result = @gearmanClient::instance($this->config)->doLowBackground( $task, serialize($data) );
		} while ($result != GEARMAN_SUCCESS && $try++ < $retry );

		if($result != GEARMAN_SUCCESS) {
			throw new \Exception(
				gearmanClient::instance($this->config)->error(),
				gearmanClient::instance($this->config)->getErrno()
			);
		}

		return $result;
	}

	public function addTaskHigh($task, $data) {
		$task = $this->getServerTaskName($task);
		$try = 0;
		$retry = $this->config['retry'] ?: 0;
		do {
			$result = @gearmanClient::instance($this->config)->doHighBackground( $task, serialize($data) );
		} while ($result != GEARMAN_SUCCESS && $try++ < $retry );

		if($result != GEARMAN_SUCCESS) {
			throw new \Exception(
				gearmanClient::instance($this->config)->error(),
				gearmanClient::instance($this->config)->getErrno()
			);
		}

		return $result;
	}

	public function listen($task, $callback) {
		$task = $this->getServerTaskName($task);
		gearmanWorker::instance($this->config)->addFunction($task, function(\GearmanJob $job) use ($callback) {
			call_user_func($callback, unserialize($job->workload()));
			if(gearman::$last_memory_checked < time() - 60) {
				memoryLimitException::check();
				gearman::$last_memory_checked = time();
			}
			return GEARMAN_SUCCESS;
		});
		$worker = gearmanWorker::instance($this->config);
		while(!gearmanWorker::instance($this->config)->terminated  && (@$worker->work() || $worker->returnCode() == GEARMAN_IO_WAIT || $worker->returnCode() == GEARMAN_NO_JOBS || $worker->returnCode() == GEARMAN_TIMEOUT)){
			if (!gearmanWorker::instance($this->config)->terminated && !@$worker->wait()) {
		        // echo $worker->timeout();
		        if ($worker->returnCode() == GEARMAN_NO_ACTIVE_FDS) {
		            continue;
		        }
		        if ($worker->returnCode() == GEARMAN_TIMEOUT) {
		            continue;
		        }
		        break;
		    }
		}
	}

	public function terminate() {
		gearmanWorker::instance($this->config)->terminated = true;
	}
}
