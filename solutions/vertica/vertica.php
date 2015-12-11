<?
namespace solutions;
use bg;
use \solutions\vertica\dbVertica;
use PDO;
use \solutions\utrack\storageDriver;
use log;

class vertica extends solution implements storageDriver {
	static public function push($table, array $data) {
		log::debug(__METHOD__.':'.print_r(func_get_args(), true));
		bg::run([__CLASS__, 'syncPush'], [$table, $data]);
	}

	static public function syncPush($table, array $data) {
		log::debug(__METHOD__.':'.print_r(func_get_args(), true));
		dbVertica::getConnect()->insert( $data, $table );
	}

	static public function query($query) {
		dbVertica::getConnect()->query($query);
	}

	static public function getNow() {
		return dbVertica::getConnect()->getNow();
	}

	static public function getToday() {
		return dbVertica::getConnect()->getToday();
	}

	static public function getRows( $params_sql, $table = null ) {
		return dbVertica::getConnect()->getRows( $params_sql, $table );
	}

	static public function delete($table, $data) {
		return dbVertica::getConnect()->delete($data, $table);
	}
}
