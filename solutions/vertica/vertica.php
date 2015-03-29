<?
namespace solutions;
use bg;

class vertica extends solution implements solutions\utrack\storageDriver {
	static public function push($table, array $data) {
		bg::run([__CLASS__, 'syncPush'], [$table, $data]);
	}

	static public function syncPush($table, array $data) {
		\solutions\vertica\dbVertica::getConnect()->insert( $data, $table );
	}

	static public function query($query) {
		\solutions\vertica\dbVertica::getConnect()->query($query);
	}

	static public function getNow() {
		return \solutions\vertica\dbVertica::getConnect()->getNow();
	}
}
