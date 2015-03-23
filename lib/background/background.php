<?
class background extends singleton {
	const PRIORITY_LOW = -1;
	const PRIORITY_NORMAL = 0;
	const PRIORITY_HIGH = 1;

	/**
	 * @var $driver backgroundDriver
	 */
	protected $driver;

	protected function __construct($config = '') {
		/**
		 * @var $driver_name backgroundDriver
		 */

		$driver_name = config::get('backgroundDriver');
		if(!class_exists($driver_name)) {
			throw new Exception("Unknown background driver '{$driver_name}'.");
		}
		if(!is_subclass_of($driver_name, 'backgroundDriver')) {
			throw new Exception("Invalid background driver '{$driver_name}'.");
		}
		$this->driver = $driver_name::instance($config);
	}

	function run($queue, $data, $priority = self::PRIORITY_NORMAL) {
		switch($priority) {
			case self::PRIORITY_HIGH:
				$this->driver->addTaskHigh($queue, $data);
				break;
			case self::PRIORITY_NORMAL:
				$this->driver->addTask($queue, $data);
				break;
			case self::PRIORITY_LOW:
				$this->driver->addTaskLow($queue, $data);
				break;
		}
	}

	function listen($queue, $callback) {
		$this->driver->listen($queue, $callback);
	}

	function terminate() {
		$this->driver->terminate();
	}
}
