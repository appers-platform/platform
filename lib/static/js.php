<?
class js extends staticParent {
	static protected $map = [];
	static protected $urls = [];

	static private $coffee_inited = false;
	static private $variables = [];
	static private $variables_sent = false;
	static private $callbacks = [];
	static private $callbacks_second = [];
	static private $callbacks_sent = false;

	static private function initCoffee() {
		if(self::$coffee_inited)
			return;
		self::$coffee_inited = true;
		require_once ROOT.'/extLib/CoffeeScript/Init.php';
		CoffeeScript\Init::load();
	}

	static function getSupportedExtensions() {
		return ['coffee', 'js'];
	}

	static function getExtension() {
		return 'js';
	}

	static function getCompiledContent($file_path, $extension, $config) {
		$content = file_get_contents($file_path);
		if($extension == 'coffee') {
			self::initCoffee();

			$prepared_content = [];
			foreach(explode("\n", $content) as $line) {
				if(substr(trim($line), 0, 1) == '#')
					continue;
				$prepared_content[] = $line;
			}

			$prepared_content = implode("\n", $prepared_content);

			if(!trim($prepared_content))
				return '';

			$content = CoffeeScript\Compiler::compile($prepared_content, [
				'filename'	=>	$file_path,
				'header'	=>	''
			]);

			if($config['wrapper']) {
				$config = array_merge($config, ['content' => $content]);
				$content = static::renderWrapper( $config['wrapper'], $config );
			}
		}
		return $content;
	}

	static public function renderHTML($group_id) {
		$urls = isset(self::$urls[$group_id]) ? self::$urls[$group_id] : [];
		$urls[] = self::getUrl($group_id);
		$result = [];

		if(!self::$variables_sent && count(self::$variables)) {
			self::$variables_sent = true;
			$result[] = '<script>var __appersVariables = '.json_encode(self::$variables).';</script>';
		}

		if($urls) {
			foreach($urls as $url)
				if($url)
					$result[] = '<script src="'.$url.'"></script>';
		}

		return implode("\n", $result);
	}

	static public function renderLastHTML() {
		$result = [];

		if(!self::$callbacks_sent && count(self::$callbacks)) {
			self::$callbacks_sent = true;
			$script = "<script>\n";
			$script .= "$(document).ready(function(){\n";
			foreach(array_merge(self::$callbacks, self::$callbacks_second) as $callback) {
				list($function, $arguments) = $callback;
				$script .= $function.'(';
				$script_arguments = [];
				foreach((array)$arguments as $argument) {
					$script_arguments[] = json_encode($argument);
				}
				$script .= implode(',', $script_arguments);
				$script .= ");\n";
			}
			$script .= "});\n";
			$script .= "</script>\n";
			$result[] = $script;
		}

		return implode("\n", $result);
	}

	static public function setVar($name, $value) {
		self::$variables[$name] = $value;
	}

	static public function getVar($name) {
		return isset(self::$variables[$name]) ? self::$variables[$name] : null;
	}

	static public function addCallback($function_name, $arguments = []) {
		self::$callbacks[] = [$function_name, $arguments];
	}

	static public function addSecondCallback($function_name, $arguments = []) {
		self::$callbacks_second[] = [$function_name, $arguments];
	}
}
