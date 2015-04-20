<?
namespace solutions;

class solution extends \renderer {
	const ENABLING_REQUIRED = false;

	static private $inited = [];
	static private $names = [];

	static private $config = [];

	static private $scripts_called = [];
	static private $called_data = false;

	static public function init() {
		$solution = get_called_class();
		if($solution == __CLASS__) {
			throw new \Exception('Direct call of method is denied.');
		}
		if(in_array($solution, self::$inited))
			return false;
		self::$inited[] = $solution;

		if(isset(\loader::getMap()[$solution])) {
			$path = str_replace('\\', '/', substr(\loader::getMap()[$solution], strlen(ROOT.'/solutions/')));
			list($solution_dir) = explode('/', $path);
		} else {
			$name = explode('\\', $solution);
			array_shift($name);
			$solution_dir = array_shift($name);
		}

		self::$names[$solution] = $solution_dir;

		\styles::scan(
			ROOT.'/solutions/'.$solution_dir.'/static',
			\styles::GROUP_SOLUTIONS,
			false,
			[ 'wrapper'	=> 'solution', 'solution_name' => $solution ]
		);

		\js::scan(
			ROOT.'/solutions/'.$solution_dir.'/static',
			\js::GROUP_SOLUTIONS,
			false,
			[ 'wrapper'	=> 'solution', 'solution_name' => $solution ]
		);

		\config::processSolution($solution_dir);

		if(static::ENABLING_REQUIRED && !static::getConfig('enabled', false)) {
			throw new \Exception('You should enable this solution in config before using');
		}

		return true;
	}

	static public function getInited() {
		return self::$inited;
	}

	static public function finalizationRendering() {
		foreach(self::$inited as $solution) {
			if(!in_array($solution, self::$scripts_called))
				$solution::callScripts();
		}
	}
	
	public function getViewPath() {
		$name = explode('\\', get_class($this));
		array_shift($name);
		$solution_name = array_shift($name);
		return '/solutions/'.$solution_name.'/view';
	}

	static public function callScripts($scripts_data = null) {
		$solution = get_called_class();
		if($solution == __CLASS__) {
			throw new \Exception('Direct call of method is denied.');
		}

		if(!in_array($solution, self::$scripts_called))
			self::$scripts_called[] = $solution;
		$scripts_data = json_encode($scripts_data);
		\js::addCallback(
			'$$.callSolutionScripts',
			[$solution, $scripts_data, self::signData($scripts_data)]
		);
	}

	static public function getSecret() {
		if(!($secret = static::getConfig('secret', false))) {
			$secret = \config::get('secret');
		}

		if(!$secret)
			throw new \Exception('Can\'t get secret phrase from config');

		return $secret;
	}

	static public function signData($data) {
		$secret = static::getSecret();
		return md5(serialize($data).$secret);
	}

	static public function checkSign($sign, $data) {
		return self::signData($data) == $sign;
	}

	static public function getCalledData($key = null, $required = false) {
		if(self::$called_data === false) {
			$data = \request::get('solution_data');
			$sign = \request::get('solution_data_sign');
			if($data && $sign) {
				if(!self::checkSign($sign, $data))
					throw new \Exception('Sign is incorrect');
				self::$called_data = json_decode($data, true);
			} else {
				self::$called_data = null;
			}
		}

		if($key) {
			if(isset(self::$called_data[$key]))
				return self::$called_data[$key];

			if($required) {
				throw new \Exception('Can\'t get required param "' . $key . '"');
			}

			return null;
		}

		return self::$called_data;
	}

	static public function setScriptData($script_data) {
		self::$called_data = $script_data;
	}

	static public function getSolutionName() {
		$solution = get_called_class();
		if($solution == __CLASS__) {
			throw new \Exception('Direct call of method is denied.');
		}

		return self::$names[$solution];
	}

	static public function mergeToConfig(array $config) {
		$solution_name = static::getSolutionName();
		self::$config[$solution_name] = \config::mergeArray(static::getConfig(null, false), $config);
	}

	static public function getConfig($name = null, $required = true, $default = null) {
		$solution = get_called_class();
		
		if($solution == __CLASS__) {
			throw new \Exception('Direct call of method is denied.');
		}

		$solution_name = static::getSolutionName();
		if(!isset(self::$config[$solution_name])) {
			self::$config[$solution_name] = [];
			if(is_file($config_file = ROOT.'/solutions/'.$solution_name.'/config.php')) {
				self::$config[$solution_name] = require $config_file;
			}
			if(!is_array(self::$config[$solution_name]))
				self::$config[$solution_name] = [];

			if($custom_config = \config::get(static::getSolutionName().'Solution')) {
				self::$config[$solution_name] = \config::mergeArray(self::$config[$solution_name], $custom_config);
			}
		}

		if(!self::$config[$solution_name]) {
			if($required) throw new \Exception('Can\'t get config for solution '.$solution_name.' "'.$solution_name.'Solution"');
			return $default;
		}

		if(!$name) {
			return self::$config[$solution_name];
		}

		if(!isset(self::$config[$solution_name][$name])) {
			if($required) throw new \Exception('Can\'t get config param "'.$name.'". Solution: '.$solution_name);
			return null;
		}

		return self::$config[$solution_name][$name];
	}

	/**
	 * @param $name
	 * @return \solutionController
	 * @throws \Exception
	 */
	static public function controller($name, $script_data = null) {
		if(get_called_class() == __CLASS__) {
			$class = '\\solutions\\'.$name.'_solutionController';
		} else {
			$class = get_called_class().'\\'.$name.'_solutionController';
		}

		if(!is_subclass_of($class, 'solutionController'))
			throw new \Exception($class.' have to be subclass of solutionController');

		/**
		 * @var \solutionController $controller
		 */
		$controller = new $class();

		if($script_data)
			$controller->setScriptData($script_data);

		return $controller;
	}

	static public function getControllerName() {
		foreach (debug_backtrace() as $trace) {
			if(!$trace['class']) continue;
			if(preg_match("/^solutions\\\\([^\\\\]+)\\\\(.*)_solutionController$/", $trace['class'], $matches)) {
				if(isset($matches[2])) {
					return $matches[2];
				}
			}
		}
		return false;
	}

	static public function getUrl($controller_name = null, array $get_params = [], $full = false) {
		if($controller_name === null)
			$controller_name = self::getControllerName();

		if(get_called_class() == __CLASS__)
			throw new \Exception('Direct call is denied.');

		$name = explode('\\', get_called_class());
		array_shift($name);
		$solution_name = array_shift($name);

		foreach (\config::get('routes') as $route => $to) {
			list($url, $params) = explode('?', $to);
			parse_str($params, $params);
			if($url != '/__solutions/'.$solution_name.'/'.$controller_name) {
				continue;
			}
			$replaces = [];
			foreach ($params as $key => $num) {
				if(!preg_match('/^\\$(\d+)$/', $num, $matches))
					continue;
				if(!isset($get_params[$key]))
					continue 2;

				$replaces[$matches[1] - 1] = $get_params[$key];
				unset($get_params[$key]);
			}

			preg_match_all('/<(string|float|int)>/', $route, $matches);
			foreach ($matches[1] as $num => $type) {
				switch ($type) {
					case 'string':
						if(!is_string($replaces[$num])) continue 2;
						break;
					case 'float':
						if(!is_float($replaces[$num])) continue 2;
						break;
					case 'int':
						if(!is_int($replaces[$num])) continue 2;
						break;
					default:
						continue 2;
						break;
				}
			}

			foreach ($replaces as $num => $replace) {
				$route = preg_replace(
					['|<string>|', '|<int>|', '|<float>|'],
					$replace,
					$route,
					1
				);
			}

			if($get_params) {
				$route .= '?'.http_build_query($get_params);
			}

			if($full) {
				$route = 'http://'.PROJECT.$route;
			}

			return $route;
		}

		if($route = array_search('/__solutions/'.$solution_name.'/'.$controller_name, $routes)) {
			if($get_params) {
				$route .= '?'.http_build_query($get_params);
			}
			return $route;
		}

		throw new \Exception('Can\'t build URL');
	}
}
