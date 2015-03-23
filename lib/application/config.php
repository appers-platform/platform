<?
class config {
	static protected $config = [];
	static protected $processed_solutions = [];

	static public function processSolution($solution_name) {
		if(in_array($solution_name, self::$processed_solutions))
			return;
		self::$processed_solutions[] = $solution_name;

		$solution_class_name = 'solutions\\'.$solution_name;

		$config = $solution_class_name::getConfig(null, false);
		if(!$config) $config = [];
		foreach($config as $name => $value) {
			$matches = [];
			if(preg_match('/^(.*)Solution$/', $name, $matches)) {
				if(isset($value['enabled']) && $value['enabled']) {
					$required_solution_class_name = 'solutions\\'.$matches[1];
					$required_solution_class_name::mergeToConfig($value);
					self::processSolution($matches[1]);
				}
			}
		}

		if($routes = $solution_class_name::getConfig('routes', false)) {
			$final_routes = [];
			foreach($routes as $url => $controller_name) {
				$final_routes[$url] = '/__'.str_replace('\\', '/', $solution_class_name).'/'.$controller_name;
			}
			self::$config['routes'] = self::mergeArray($final_routes, (array) self::$config['routes']);
		}

		if($auto_load = $solution_class_name::getConfig('autoLoad', false)) {
			self::$config['autoLoad'] = self::mergeArray((array) self::$config['autoLoad'], $auto_load);
		}

		if($listeners = $solution_class_name::getConfig('listeners', false)) {
			self::$config['listeners'] = self::mergeArray((array) self::$config['listeners'], $listeners);
		}
	}

	static public function init()  {
		self::$config = require ROOT.'/config/default.php';
		if(is_file(PROJECT_ROOT.'/config/parent.php')) {
			self::$config = self::mergeArray(
				self::$config,
				require PROJECT_ROOT.'/config/parent.php'
			);
		}

		if($env = @include(CONFIG_ROOT.'/_env.php')) {
			self::$config = self::mergeArray(
				self::$config,
				require CONFIG_ROOT.'/'.$env.'.php'
			);

			if(is_file(PROJECT_ROOT.'/config/'.$env.'.php')) {
				self::$config = self::mergeArray(
					self::$config,
					require PROJECT_ROOT.'/config/'.$env.'.php'
				);
			}
		}

		foreach(self::$config as $name => $value) {
			$matches = [];
			if(preg_match('/^(.*)Solution$/', $name, $matches)) {
				if(isset($value['enabled']) && $value['enabled']) {
					self::processSolution($matches[1]);
				}
			}
		}

		foreach(\solutions::getInited() as $solution_name => $solution_class_name) {
			if(isset(self::$config[$solution_name.'Solution']) && is_array(self::$config[$solution_name.'Solution'])) {
				$solution_class_name::mergeToConfig(self::$config[$solution_name.'Solution']);
			}
			if(is_array($config = $solution_class_name::getConfig(null, false))) {
				self::$config[$solution_name.'Solution'] = $config;
			}
		}

		if($autoload = self::get('autoLoad')) {
			if(!is_array($autoload))
				$autoload[] = $autoload;
			foreach($autoload as $function) {
				if(!is_callable($function))
					throw new Exception('AutoLoad function is not callable');
				call_user_func_array($function, []);
			}
		}

		if($listeners = self::get('listeners')) {
			if(!is_array($listeners))
				$listeners[] = $listeners;
			foreach($listeners as $listener) {

				foreach ($listener as $event => $callback) {
					\event::addCallback($event, $callback);
				}

			}
		}
	}

	static public function get($param = null, $default = null) {
		if($param === null)
			return self::$config;

		return isset(self::$config[$param]) ? self::$config[$param] : $default;
	}

	static public function mergeArray($array1, $array2) {
		if(!is_array($array1)) {
			$array1 = [];
		}

		if(!is_array($array2)) {
			$array2 = [];
		}

		if(!\helper::isAssoc($array1) && !\helper::isAssoc($array2)) {
			return array_merge($array1, $array2);
		}

		foreach($array2 as $k => $v) {
			if(isset($array1[$k])) {
				if(is_array($array1[$k]) && is_array($v)) {
					$array1[$k] = self::mergeArray($array1[$k], $v);
				} else {
					$array1[$k] = $v;
				}
			} else {
				$array1[$k] = $v;
			}
		}

		return $array1;
	}
}
