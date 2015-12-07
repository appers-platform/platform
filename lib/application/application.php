<?
class application {
	/**
	 * @var controller $controller
	 */
	static protected $controller;

	/**
	 * @return controller
	 */
	static public function getController() {
		return self::$controller;
	}

	static public function run($url, $process_404 = true) {
		list($url) = explode( '?', $url );
		$url = array_diff(explode('/', $url), ['']);
		if(count($url) && ($url[min(array_keys($url))] == '__solutions')) {
			if($process_404) {
				self::run('error/404', false);
				return;
			} else {
				throw new Exception('Can\'t process 404.');
			}
		}

		$routes = config::get('routes');
		if(is_array($routes)) {
			foreach($routes as $pattern => $result) {
				$pattern = str_replace([
					'<string>',
					'<int>',
					'<float>',
					'/'
				], [
					'([0-9a-zA-Z_\\-]+)',
					'([0-9]+)',
					'(?:\d+|\d*\.\d+)',
					'\\/'
				], $pattern);
				$pattern = '/^'.$pattern.'$/';
				$count = 0;
				if($matched = preg_replace($pattern, $result, '/'.implode('/', $url), -1, $count)) {
					if(!$count)
						continue;
					$matched = parse_url($matched);
					$url = array_diff(explode('/', isset($matched['path']) ? $matched['path'] : ''), ['']);
					if(isset($matched['query'])) {
						$arr = [];
						parse_str($matched['query'], $arr);
						$_REQUEST = array_merge($_REQUEST, $arr);
						$_GET = array_merge($_GET, $arr);
					}

					break;
				}
			}
		}

		$controller_name = implode('_', $url).'_controller';
		array_push($url, 'index');
		$controller_name2 = implode('_', $url).'_controller';

		if(!self::processAction($controller_name)) {
			if(!self::processAction($controller_name2)) {
				if($process_404) {
					self::run('error/404', false);
				} else {
					throw new Exception('Can\'t process 404.');
				}
			}
		}
	}

	static protected function processAction($controller_name) {
		if(loader::autoLoad($controller_name, false)) {
			if(is_subclass_of($controller_name, 'controller')) {

				self::$controller = new $controller_name();
				context::instance()->controller = self::$controller;
				self::$controller->setTitle(self::$controller->getDefaultTitle());

				event::fire('beforeControllerRun');

				response::ob_start();
				self::$controller->first();

				if(request::isGET())
					self::$controller->get();

				if(request::isPOST())
					self::$controller->post();

				self::$controller->last();
				self::$controller->setOutput( response::ob_end() );

				event::fire('afterControllerRun');

				styles::scan(PROJECT_ROOT.self::$controller->getViewPath(), styles::GROUP_CONTROLLER, self::$controller->getLastName());
				styles::scan(PROJECT_ROOT.self::$controller->getViewPath(), styles::GROUP_CONTROLLER, 'styles');
				styles::scan(ROOT.'/static/styles', styles::GROUP_PUBLIC);
				styles::scan(PROJECT_ROOT.'/static/styles', styles::GROUP_PUBLIC);

				js::scan(PROJECT_ROOT.self::$controller->getViewPath(), js::GROUP_CONTROLLER, self::$controller->getLastName());
				js::scan(PROJECT_ROOT.self::$controller->getViewPath(), js::GROUP_CONTROLLER, 'scripts');
				js::scan(ROOT.'/static/scripts', js::GROUP_PUBLIC);
				js::scan(PROJECT_ROOT.'/static/scripts', js::GROUP_PUBLIC);

				if(self::$controller->getOutput() === false) {
					event::fire('beforeControllerRender');
					self::$controller->render();
					event::fire('afterControllerRender');
				}

				if(!response::isSent()) {
					response::setHeader('Content-Type: '.self::$controller->getType(), null);
					response::send(self::$controller->getOutput());
				}

				return true;
			}
		}
		return false;
	}

	static public function renderMeta($with_scripts = true, $with_styles = true) {
		$result = 
			'<title>'.application::getController()->getTitle().'</title>'."\n".
			($with_scripts ? self::renderScripts() : '').
			($with_scripts ? self::renderStyles() : '').
			'<meta charset="utf-8">'."\n"
			;

		foreach (self::$controller->getMeta() as $params) {
			$result .= "<meta ";
			foreach ($params as $name => $value) {
				$result .= $name.'="'.str_replace('"', '\\"', $value).'" ';
			}
			$result .= ">\n";
		}

		return $result;
	}

	static public function renderScripts() {
		return js::renderHTML(js::GROUP_PUBLIC)."\n".
			js::renderHTML(js::GROUP_SOLUTIONS)."\n".
			js::renderHTML(js::GROUP_CONTROLLER)."\n".
			js::renderLastHTML()."\n";
	}

	static public function renderStyles() {
		return styles::renderHTML(styles::GROUP_PUBLIC)."\n".
			styles::renderHTML(styles::GROUP_SOLUTIONS)."\n".
			styles::renderHTML(styles::GROUP_CONTROLLER)."\n";
	}

	static public function getExecutingTime() {
		return round(microtime(true) - TIME_START, 6);
	}

	/**
	 * @return float Megabytes
	 */
	public static function getMemoryUsage() {
		$pid = getmypid();
		return ((((double) `ps orss -p  {$pid} | grep -v RSS`))/1024);
	}

	/**
	 * @return float Megabytes
	 */
	public static function getMemoryLimit() {
		$memory_limit = ini_get('memory_limit');
		if (preg_match('/^(\d+)(.)$/', $memory_limit, $matches)) {
			if ($matches[2] == 'M') {
				$memory_limit = $matches[1] * 1024 * 1024; // nnnM -> nnn MB
			} else if ($matches[2] == 'K') {
				$memory_limit = $matches[1] * 1024; // nnnK -> nnn KB
			}
		}

		return $memory_limit/1024/1024;
	}

	public static function echoException(Exception $error) {
		ob_start();
		print '<pre class="AppersException">';
		print '<b>Exception ['.$error->getCode().']:'.$error->getMessage().'</b>'."\n";
		print $error->getTraceAsString();
		print '</pre>';
		$result = ob_get_contents();
		ob_end_clean();
		return $result;
	}

	public static function getControllerName() {
		preg_match('/^(.*)_controller$/', get_class(self::getController()), $matches);
		if(isset($matches[1]))
			return $matches[1];

		throw new Exception("Can\'t get controller name");
	}

	static public function getUrl($controller_name = null, array $get_params = [], $full = false) {
		if($controller_name === null)
			$controller_name = self::getControllerName();

		if(get_called_class() == __CLASS__)
			throw new \Exception('Direct call is denied.');

		foreach (\config::get('routes') as $route => $to) {
			list($url, $params) = explode('?', $to);
			parse_str($params, $params);
			if($url != '/'.$controller_name) {
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

		if($route = array_search('/'.$controller_name, $routes)) {
			if($get_params) {
				$route .= '?'.http_build_query($get_params);
			}
			return $route;
		}

		throw new \Exception('Can\'t build URL');
	}
}
