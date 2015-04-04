<?
namespace solutions\utrack;

class cases {
	static protected $types = [
		'data_source',
		'data_name',
		'data_value',
		'event_name',
		'event_value'
	];
	static public $types_cache = [];

	static public function getId($type, $value) {
		if(!in_array($type, self::$types))
			throw new Exception("Type '{$type}' is not allowed");

		if(!isset(self::$types_cache[$type]))
			self::$types_cache[$type] = [];

		$hash = md5($value);

		if(isset(self::$types_cache[$type][$hash]))
			self::$types_cache[$type][$hash];
			
		$driver = \solutions\utrack::getDriverClass();

		do {
			$rows = $driver::getRows([ 'hash' => $hash ], $table = 'solutions_utrack_cases_'.$type);
			if(count($rows)) {
				self::$types_cache[$type][$hash] = $rows[0]['id'];
				return $rows[0]['id'];
			}
			$driver::syncPush($table, [
				'hash'	=> $hash,
				'value'	=> $value
			]);
		} while (++$i<2);
		throw new Exception("Error");
		
	}

	static public function getTypes() {
		return self::$types;
	}
}
