<?
namespace solutions;
use bg;
use \solutions\vertica\dbVertica;
use PDO;
use \solutions\utrack\storageDriver;

class vertica extends solution implements storageDriver {
	static public function push($table, array $data) {
		bg::run([__CLASS__, 'syncPush'], [$table, $data]);
	}

	static public function syncPush($table, array $data) {
		dbVertica::getConnect()->insert( $data, $table );
	}

	static public function query($query) {
		dbVertica::getConnect()->query($query);
	}

	static public function getNow() {
		return dbVertica::getConnect()->getNow();
	}

	static public function getRows( $params_sql, $table = null ) {
		return dbVertica::getConnect()->getRows( $params_sql, $table );
	}
}
