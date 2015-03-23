<?
class singleton {
	static private $storage;

	protected function __construct() {}

	/**
	 * @return static
	 * @throws Exception
	 */
	final static public function instance() {
		$class_name = get_called_class();
		if($class_name == __CLASS__)
			throw new \Exception('Direct call of method is denied.');

		$key = $class_name.'_'.md5(serialize(func_get_args()));

		if(!isset(self::$storage[$key])) {
			$reflect  = new ReflectionClass($class_name);
			self::$storage[$key] = $reflect->newInstanceWithoutConstructor();
			call_user_func_array([self::$storage[$key], '__construct'], func_get_args());
		}

		return self::$storage[$key];
	}
}
