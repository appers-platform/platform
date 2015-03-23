<?
class helper {
	static function isAssoc(array $array) {
		return array_keys($array) !== range(0, count($array) - 1);
	}

	static public function code($str, $password = '') {
		$str = (string) $str;
		$salt = 'Osh(6&&*(&(';
		$len = strlen($str);
		$gamma = '';
		$n = $len > 100 ? 8 : 2;
		while( strlen($gamma) < $len ) {
			$gamma .= substr(pack('H*', sha1($password.$gamma.$salt)), 0, $n);
		}
		return $str^$gamma;
	}

	static public function encode($str, $password) {
		return (base64_encode(self::code(serialize($str), (string) $password)));
	}

	static public function decode($str, $password) {
		return unserialize(self::code(base64_decode(($str)), (string) $password));
	}

	static public function generatePassword() {
		return substr(md5(rand(0, 9999).time().microtime(true)), 0, 7);
	}

	static public function fullUrl($url) {
		return \request::getProtocol().'://'.\request::getHost().$url;
	}

	static public function getParentClasses($object_or_classname) {
		if(is_object($object_or_classname)) $object_or_classname = get_class($object_or_classname);
		if(!is_string($object_or_classname))
			throw new Exception('Unknown type of $object_or_classname');
		$result = [];
		while($object_or_classname = get_parent_class($object_or_classname)) {
			$result[] = $object_or_classname;
		}

		return $result;
	}

	static public function getClasses($object_or_classname) {
		if(is_object($object_or_classname)) $object_or_classname = get_class($object_or_classname);
		if(!is_string($object_or_classname))
			throw new Exception('Unknown type of $object_or_classname');
		$result = [ $object_or_classname ];
		while($object_or_classname = get_parent_class($object_or_classname)) {
			$result[] = $object_or_classname;
		}

		return $result;
	}

	static public function compareArrays($array1, $array2, $array2_may_have_additional_elements = false) {
		foreach ($array1 as $key => $value) {
			if(!isset($array2[$key]))
				return false;
			if($array2[$key] != $value)
				return false;
		}

		if(!$array2_may_have_additional_elements) {
			foreach ($array2 as $key => $value) {
				if(!isset($array1[$key]))
					return false;
				if($array1[$key] != $value)
					return false;
			}
		}

		return true;
	}
}
