<?
class i18n {
	static protected $locale;
	static protected $yaml_cache = [];

	static public function getLocale() {
		return self::$locale;
	}

	static public function setLocale($locale) {
		self::$locale = $locale;
	}

	static public function getCache($locale_to) {
		$db = debug_backtrace();
		$call = [];
		foreach($db as $call) {
			if($call['file'] && $call['file'] != __FILE__)
				break;
		}
		$file = str_replace('\\', '/', $call['file']);

		if(substr($file, 0, $st_len = strlen(ROOT.'/solutions/')) == ROOT.'/solutions/') {
			$file = (substr($file, $st_len, strpos($file, '/', $st_len) - $st_len));
			$i18n_path = PROJECT_ROOT.'/solutions/'.$file.'/'.$locale_to.'.yaml';
			if(!is_file($i18n_path))
				$i18n_path = ROOT.'/solutions/'.$file.'/i18n/'.$locale_to.'.yaml';
		} else {
			$i18n_path = PROJECT_ROOT.'/i18n/'.$locale_to.'.yaml';
		}

		if(!isset(self::$yaml_cache[$i18n_path])) {
			if(is_file($i18n_path)) {
				self::$yaml_cache[$i18n_path] = yaml::parseFile($i18n_path);
				if(self::$yaml_cache[$i18n_path] === null) {
					self::$yaml_cache[$i18n_path] = false;
				}
			} else {
				self::$yaml_cache[$i18n_path] = false;
			}
		}

		return self::$yaml_cache[$i18n_path];
	}

	static public function translate($text, $locale_to) {
		$cache = self::getCache($locale_to);

		if(!isset($cache['translates'][$text]))
			return $text;

		return $cache['translates'][$text];
	}

	static public function getOrigLocale() {
		$cache = self::getCache('orig');
		return $cache['locale'];
	}

	static public function __() {
		// $ -> orig -> current

		$arguments = func_get_args();
		$options = is_array(end($arguments)) ? array_pop($arguments) : array();

		$locale = self::getLocale();
		if (isset($options['locale'])) {
			$locale = $options['locale'];
		}

		$phrase = array_shift($arguments);
		if(($orig_phrase = self::translate($phrase, 'orig')) !== false) {
			$phrase = $orig_phrase;
		}
		if($locale && self::getOrigLocale() != $locale) {
			$phrase = self::translate($phrase, $locale);
		}

		if (count($arguments)) {
			array_unshift($arguments, $phrase);
		}
		$phrase = ($arguments ? call_user_func_array('sprintf', $arguments) : $phrase);

		return $phrase;
	}


}

function __() {
	return call_user_func_array(['i18n','__'], func_get_args());
}
