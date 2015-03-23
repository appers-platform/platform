<?
class styles extends staticParent {
	static protected $map = [];
	static protected $urls = [];

	static function getSupportedExtensions() {
		return ['less', 'css'];
	}

	static function getExtension() {
		return 'css';
	}

	static function getCompiledContent($file_path, $extension, $config) {
		$content = file_get_contents($file_path);
		if($extension == 'less') {
			require_once ROOT.'/extLib/lessc.inc.php';
			$less = new lessc();
			$content = $less->compile($content);
		}
		return $content;
	}

	static public function renderHTML($group_id) {
		$urls = isset(self::$urls[$group_id]) ? self::$urls[$group_id] : [];
		$urls[] = self::getUrl($group_id);
		$result = [];
		if($urls) {
			foreach($urls as $url)
				if($url)
					$result[] = '<link rel="stylesheet" type="text/css" href="'.$url.'"/>';
		}

		return implode("\n", $result);
	}
}
