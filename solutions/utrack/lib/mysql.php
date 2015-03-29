<?
namespace solutions\utrack;
use bg;
use dbMysql;

class mysql extends \solutions\solution implements storageDriver {
	static public function push($table, array $data) {
		bg::run([__CLASS__, 'syncPush'], [$table, $data]);
	}

	static public function syncPush($table, array $data) {
		$table = 'solutions_utrack_'.$table;

		$values = [];
		$keys = [];
		foreach ( $data as $k => $v ) {
			$keys[] = ":{$k}";
			$values[":{$k}"] = $v;
		}

		$sql = "INSERT INTO {$table}(`" .
			implode('`, `', array_keys($data)) .
		"`) VALUES(" .
			implode(', ', $keys) .
		')';

		dbMysql::getConnect(static::getConfig('mysql_connection_name', false, null))
			->query($sql, $values, $false);
	}

	static public function query($query) {
		dbMysql::getConnect(static::getConfig('mysql_connection_name', false, null))
			->query($query);
	}

	static public function getNow() {
		return dbMysql::getConnect(static::getConfig('mysql_connection_name', false, null))->getNow();
	}
}
