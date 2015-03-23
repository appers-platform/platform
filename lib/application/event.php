<?
class event {
	static protected $callbacks = [];

	static public function fire($name, array $data = []) {
		if($solution = \solutions::getRunnerSolution()) {
			$name = 'solutions.user.'.$name;
		}

		if(!isset(self::$callbacks[$name])) return;
		foreach(self::$callbacks[$name] as $callback) {
			if(!is_callable($callback))
				throw new Exception('Callback is not callable');
			call_user_func_array($callback, $data);
		}
	}

	static public function addCallback($name, $callback) {
		if(!isset(self::$callbacks[$name]))
			self::$callbacks[$name] = [];
		self::$callbacks[$name][] = $callback;
	}

	static public function removeCallbacks($name) {
		self::$callbacks[$name] = [];
	}
}