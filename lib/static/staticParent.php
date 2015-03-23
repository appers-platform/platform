<?
abstract class staticParent {
	const GROUP_PUBLIC = 0;
	const GROUP_CONTROLLER = 1;
	const GROUP_SOLUTIONS = 2;

	/**
	 * @return Array
	 * @throws Exception
	 */
	static function getSupportedExtensions() {
		throw new Exception("You should override this method.");
	}

	/**
	 * @param $group_id
	 * @throws Exception
	 */
	static function renderHTML($group_id) {
		throw new Exception("You should override this method.");
	}

	/**
	 * @return string
	 * @throws Exception
	 */
	static function getExtension() {
		throw new Exception("You should override this method.");
	}

	static protected function renderWrapper($wrapper, $context) {
		ob_start();

		foreach($context as $k => $v)
			$$k = $v;

		require dirname(__FILE__).'/wrappers/'.$wrapper.'_'.get_called_class().'.php';

		$result = ob_get_contents();
		ob_end_clean();

		return $result;
	}

	/**
	 * @param string $file_path
	 * @param string $extension
	 * @throws Exception
	 * @return string
	 */
	static function getCompiledContent($file_path, $extension, $config) {
		throw new Exception("You should override this method.");
	}

	static public function scan($path, $group_id, $prefix = false, $config = []) {
		if(!is_dir($path))
			return;

		$file_info = new SplFileInfo($path);
		$ri = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator($file_info->getRealPath()),
			\RecursiveIteratorIterator::SELF_FIRST
		);

		$static = [];

		foreach ($ri as $file) {
			/**
			 * @var SplFileInfo $file
			 */
			if (!in_array(strtolower($file->getExtension()), static::getSupportedExtensions())) {
				continue;
			}

			if ($prefix) {
				if($file->getBasename('.'.$file->getExtension()) != $prefix) {
					continue;
				}
			}

			if(!isset(static::$map[$group_id])) {
				static::$map[$group_id] = [];
			} else {
				foreach(static::$map[$group_id] as $f) {
					if($f['path'] == $file->getPathname())
						continue 2;
				}
			}

			$static[$file->getFilename()] = [
				'path'		=> $file->getPathname(),
				'extension'	=> $file->getExtension(),
				'm_time'	=> $file->getMTime(),
				'size'		=> $file->getSize(),
				'config'	=> $config
			];
		}

		ksort($static, SORT_NATURAL);
		foreach($static as $file) {
			static::$map[$group_id][] = $file;
		}
	}

	static public function addUrl($url, $group_id = self::GROUP_PUBLIC) {
		if(!isset(static::$urls[$group_id]))
			static::$urls[$group_id] = [];
		if(!in_array($url, static::$urls[$group_id]))
			static::$urls[$group_id][] = $url;
	}

	static protected function getUrl($group_id) {
		if(!isset(static::$map[$group_id]) || !count(static::$map[$group_id]))
			return;

		$current_checksum = md5(serialize(static::$map[$group_id]));

		$file_name = $current_checksum.'.'.static::getExtension();

		if(!is_dir(ROOT.'/cache/'.PROJECT)) {
			mkdir(ROOT.'/cache/'.PROJECT);
		}
		$file_path = ROOT.'/cache/'.PROJECT.'/'.$file_name;
		if(!is_file($file_path)) {
			$content = '';
			foreach(static::$map[$group_id] as $file) {
				$content .= static::getCompiledContent($file['path'], $file['extension'], $file['config']);
			}
			file_put_contents($file_path, $content);
		}

		return '/static/'.$file_name;
	}
}
