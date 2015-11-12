<?
class loader {
	static protected $cache = false;
	static protected $files_list_md5 = false;
	static protected $files_deep_md5 = false;
	static protected $ext_lib_len;
	static protected $ext_autoload = [];
	static protected $files_loaded = [];
	static protected $map_generated = false;
	static protected $is_files_changed = null;
	static protected $inited = false;

	static protected $directories = [ 'extLib', 'lib', 'solutions', 'tasks', 'controller' ];

	static public function checkFile(SplFileInfo $file) {
		if ($file->getExtension() != 'php')
			return false;

		if(!self::$ext_lib_len) {
			self::$ext_lib_len = strlen(ROOT.'/extLib/');
		}

		if(str_replace('\\', '/', substr($file, 0, self::$ext_lib_len)) == ROOT.'/extLib/') {
			$ext = (str_replace('\\', '/', substr($file, self::$ext_lib_len)));
			$ext = substr($ext, 0, strpos($ext, '/'));
			if(!$ext)
				return false;
			if(!isset($ext_autoload[$file_path = ROOT.'/extLib/'.$ext.'/_autoloaded'])) {
				$ext_autoload[$file_path] = file_exists($file_path);
			}

			return $ext_autoload[$file_path];
		}

		return true;
	}

	static public function flushCache() {
		self::$cache = false;
		self::$ext_lib_len = null;
	}

	static public function flushCacheFile() {
		unlink(ROOT . '/cache/'.PROJECT.'-load.php');
		self::$map_generated = false;
	}

	static public function generateCache($force = false) {
		if(self::$map_generated && !$force)
			return;
		self::$map_generated = true;

		$class_map = [];
		$files_list = [];

		foreach([ROOT, PROJECT_ROOT] as $dir_path) {
			foreach(self::$directories as $dir) {
				$dir = $dir_path.'/'.$dir;

				if(!is_dir($dir))
					continue;

				$file_info = new SplFileInfo($dir);
				$ri = new \RecursiveIteratorIterator(
					new \RecursiveDirectoryIterator($file_info->getRealPath()),
					\RecursiveIteratorIterator::SELF_FIRST
				);

				foreach ($ri as $file) {
					if(!self::checkFile($file))
						continue;
					$files_list[] = str_replace('\\', '/', $file->getPathname());
					foreach(self::getClasses( file_get_contents(str_replace('\\', '/', $file->getPathname())) ) as $class) {
						$class_map[$class] = str_replace('\\', '/', $file->getPathname());
					}
				}
			}
		}

		$class_map['$md5'] = md5(serialize($files_list));

		self::$cache = $class_map;
		$export = '<?php return '.var_export($class_map, true).';';

		file_put_contents(ROOT.'/cache/'.PROJECT.'-load.php', $export);
	}

	static public function getFilesListMd5($force = false) {
		if(self::$files_list_md5 && !$force)
			return self::$files_list_md5;

		$files_list = [];

		foreach([ROOT, PROJECT_ROOT] as $dir_path) {
			foreach(self::$directories as $dir) {
				$dir = $dir_path.'/'.$dir;

				if(!is_dir($dir))
					continue;

				$file_info = new SplFileInfo($dir);
				$ri = new \RecursiveIteratorIterator(
					new \RecursiveDirectoryIterator($file_info->getRealPath()),
					\RecursiveIteratorIterator::SELF_FIRST
				);

				foreach ($ri as $file) {
					if(!self::checkFile($file))
						continue;
					$files_list[] = str_replace('\\', '/', $file->getPathname());
				}
			}
		}

		self::$files_list_md5 = md5(serialize($files_list));
		return self::$files_list_md5;
	}

	static public function getFilesContentMd5($force = false) {
		if(self::$files_deep_md5 && !$force)
			return self::$files_deep_md5;

		$files_list = [];

		foreach([ROOT, PROJECT_ROOT] as $dir_path) {
			foreach(self::$directories as $dir) {
				$dir = $dir_path.'/'.$dir;

				if(!is_dir($dir))
					continue;

				$file_info = new SplFileInfo($dir);
				$ri = new \RecursiveIteratorIterator(
					new \RecursiveDirectoryIterator($file_info->getRealPath()),
					\RecursiveIteratorIterator::SELF_FIRST
				);

				foreach ($ri as $file) {
					if(!self::checkFile($file))
						continue;
					$files_list[md5(file_get_contents($file->getPathname()))] = str_replace('\\', '/', $file->getPathname());
				}
			}
		}

		self::$files_deep_md5 = md5(serialize($files_list));
		return self::$files_deep_md5;
	}

	static public function flushContentCache() {
		self::$is_files_changed = null;
	}

	static public function flushContentCacheFile($unlink = true) {
		$file = ROOT.'/cache/'.PROJECT.'-content-md5.txt';
		if($unlink && is_file($file)) {
			unlink($file);
		} else {
			file_put_contents($file, md5(time()));
		}
		self::$is_files_changed = null;
	}

	static public function isFilesChanged() {
		if(self::$is_files_changed !== null)
			return self::$is_files_changed;

		if(is_file($file = ROOT.'/cache/'.PROJECT.'-content-md5.txt')) {
			if(file_get_contents($file) == self::getFilesContentMd5()) {
				self::$is_files_changed = false;
				return false;
			}
		}

		file_put_contents($file, self::getFilesContentMd5());
		self::$is_files_changed = true;
		return true;
	}

	static public function getClassesList() {
		if(self::$cache === false && is_file(ROOT.'/cache/'.PROJECT.'-load.php')) {
			self::$cache = require(ROOT . '/cache/'.PROJECT.'-load.php');
		}
		if(!is_array(self::$cache))
			self::generateCache();

		return array_keys(self::$cache);
	}

	static public function getMap() {
		if(self::$cache === false && is_file(ROOT.'/cache/'.PROJECT.'-load.php')) {
			self::$cache = require(ROOT . '/cache/'.PROJECT.'-load.php');
		}
		if(!is_array(self::$cache))
			self::generateCache();

		return self::$cache;
	}

	static public function getClasses($php_code) {
		$classes = array();
		$tokens = token_get_all($php_code);
		$count = count($tokens);
		$namespace = '';
		for ($i = 2; $i < $count; $i++) {
			if (   $tokens[$i - 2][0] == T_NAMESPACE
				&& $tokens[$i - 1][0] == T_WHITESPACE
				&& $tokens[$i][0] == T_STRING) {

				$namespace = '';
				for($j = $i; $j < $count; $j++){
					if(in_array($tokens[$j][0], [ T_STRING, T_NS_SEPARATOR ])) {
						$namespace .= $tokens[$j][1];
					} else {
						break;
					}
				}
			}

			if (   ( $tokens[$i - 2][0] == T_CLASS || $tokens[$i - 2][0] == T_INTERFACE || $tokens[$i - 2][0] == T_TRAIT )
				&& $tokens[$i - 1][0] == T_WHITESPACE
				&& $tokens[$i][0] == T_STRING) {
				$class_name = $tokens[$i][1];
				if($namespace) {
					$class_name = $namespace.'\\'.$class_name;
				}
				$classes[] = $class_name;
			}
		}
		return $classes;
	}

	static protected function loadFile($file, $class_name = false) {
		if(in_array($file, array_keys(self::$files_loaded)))
			return self::$files_loaded[$file];
#		print "loading: {$class_name}: {$file}\n";
		self::$files_loaded[$file] = @include $file;
		return self::$files_loaded[$file];
	}

	static public function autoLoad($class, $throw = true) {
		$backtrace = debug_backtrace();
		$backtrace = array_pop($backtrace);

		if(substr($backtrace['file'], 0, strlen(ROOT.'/extLib/')) == ROOT.'/extLib/')
			$throw = false;

		if(substr($class, 0, 1) == '\\')
			$class = substr($class, 1);

#		print "autoLoad: {$class}\n";

		if(self::$cache === false && is_file(ROOT.'/cache/'.PROJECT.'-load.php')) {
			self::$cache = require(ROOT . '/cache/'.PROJECT.'-load.php');
		}
		if(!is_array(self::$cache)) {
			self::generateCache();
		}

		if(self::getFilesListMd5() != self::$cache['$md5'] && !isset(self::$cache[$class])) {
			self::generateCache();
		}

		if(isset(self::$cache[$class])) {
			self::loadFile(self::$cache[$class], $class);
		} else {
			if(preg_match('/^__solutions_([0-9a-zA-z_\\-\\\\]+)$/', $class)) {
				eval("class {$class} extends \\solutionLoaderController {}");
			} else if(preg_match('/^([0-9a-zA-z_\\-\\\\]+)TModel$/', $class)) {
				$ns = explode('\\', $class);
				$class_name = array_pop($ns);
				$ns = implode('\\', $ns);
				if($ns) $ns = 'namespace '.$ns.'; ';
				eval("{$ns} class {$class_name} extends \\TModel {}");
			} else if(preg_match('/^([0-9a-zA-z_\\-\\\\]+)Model$/', $class)) {
				$ns = explode('\\', $class);
				$class_name = array_pop($ns);
				$ns = implode('\\', $ns);
				if($ns) $ns = 'namespace '.$ns.'; ';
				eval("{$ns} class {$class_name} extends \\model {}");
			} else if(preg_match('/^([0-9a-zA-z_\\-\\\\]+)_controller$/', $class)) {
				if(is_file($path = controller::getControllerFileName($class))) {
					eval("class {$class} extends controller { public function first() { require '$path'; } }");
				} else if(is_file(controller::getViewFileName($class))) {
					eval("class {$class} extends controller {}");
				}
			} else if(preg_match('/^([0-9a-zA-z_\\-\\\\]+)_solutionController$/', $class)) {
				if(is_file($path = solutionController::getControllerFileName($class))) {
					$ns = explode('\\', $class);
					$class_name = array_pop($ns);
					$ns = implode('\\', $ns);
					if($ns) $ns = 'namespace '.$ns.'; ';
					eval("{$ns} class {$class_name} extends \\solutionController { public function first() { require '$path'; } }");
				} else if(is_file(solutionController::getViewFileName($class))) {
					$ns = explode('\\', $class);
					$class_name = array_pop($ns);
					$ns = implode('\\', $ns);
					if($ns) $ns = 'namespace '.$ns.'; ';
					eval("{$ns} class {$class_name} extends \\solutionController {}");
				}
			}
		}

		if(!class_exists($class, false) && self::getFilesListMd5() != self::$cache['$md5']) {
			self::generateCache();
			if(isset(self::$cache[$class]) && !interface_exists($class) && !class_exists($class))
				self::loadFile(self::$cache[$class], $class);
		}

		if(class_exists($class, false)) {
			if(is_subclass_of($class, 'solutions\\solution')) {
				if($class != 'solutionController') {
					$class::init();
				}
			}
			return true;
		} else if(!interface_exists($class, false)) {
			if($throw)
				throw new Exception('Can\'t load "'.$class.'"');
			return false;
		}

		return true;
	}

	static public function init($force = false) {
		if(self::$inited && !$force) return;
		self::$inited = true;
		spl_autoload_register('loader::autoLoad');
		require_once ROOT.'/lib/application/define.php';
		require_once ROOT.'/lib/i18n.php';
	}
}
