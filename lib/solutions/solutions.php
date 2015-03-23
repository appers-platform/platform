<?
class solutions {
	static public function controller($solution_name, $controller_name) {
		return \solutions\solution::controller($solution_name.'\\'.$controller_name);
	}

	static public function getRunnerSolution() {
		foreach(debug_backtrace() as $step_of_call) {
			if(isset($step_of_call['class']) && $step_of_call['class']) {
				if($step_of_call['class'] == 'bg')
					return false;
				if($class = self::getSolutionFromClassName($step_of_call['class']))
					return $class;
			}
			if(isset($step_of_call['object']) && $step_of_call['object'])
				if($class = self::getSolutionFromClassName(get_class($step_of_call['object'])))
					return $class;
		}

		return false;
	}

	static protected function getSolutionFromClassName($class_name) {
		$matches = null;
		preg_match('/^solutions\\\([^\\\]+)/', $class_name, $matches);
		if(!$matches || count($matches) < 2) {
			return false;
		}

		return $matches[1];
	}

	static public function getStoragePrefix() {
		if(!($prefix = self::getRunnerSolution())) {
			return '';
		}

		$prefix = '/solutions/'.$prefix.'/';
		return str_replace('/', '-', $prefix);
	}

	static public function getInited() {
		$result = [];
		foreach(\solutions\solution::getInited() as $class) {
			$name = explode('\\', $class);
			array_shift($name);
			$result[implode('\\', $name)] = $class;
		}

		return $result;
	}

	static public function checkInited($solution_name) {
		return in_array($solution_name, array_keys(self::getInited()));
	}
}
