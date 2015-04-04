<?
class renderer extends stdClass {
	public function renderFile($file, $context, $silent = false) {
		if(!is_file($file)) {
			if($silent) return '';
			throw new Exception('File "'.$file.'" not found.');
		}

		foreach($context as $k => $v)
			$$k = $v;

		ob_start();
		include $file;
		return ob_get_clean();
	}

	public function __get($name) {
		if(!isset($this->$name))
			return null;

		return $this->$name;
	}

	static public function getLayoutPath($name) {
		if(is_file($file_path = PROJECT_ROOT.'/layout/'.$name.'.view.php'))
			return $file_path;

		if(is_file($file_path = ROOT.'/layout/'.$name.'.view.php'))
			return $file_path;

		throw new Exception('Can\'t find layout "'.$name.'"');
	}

	public function renderPartial($name, array $context = []) {
		if(($this instanceof solutionController) || ($this instanceof solutions\solution)) {
			$file = PROJECT_ROOT.dirname($this->getViewPath()).'/_partials/'.$name.'.php';
			if(!is_file($file)) {
				$file = ROOT.$this->getViewPath().'/_partials/'.$name.'.php';
			}
		} else {
			$file = PROJECT_ROOT.$this->getViewPath().'/_partials/'.$name.'.php';
		}

		if(!is_file($file))
			throw new Exception('File "'.$file.'" not found.');

		foreach($context as $k => $v)
			$$k = $v;

		ob_start();
		include $file;
		return ob_get_clean();
	}
}