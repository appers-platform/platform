<?
namespace solutions;
use bg;

class vertica extends solution {
	static public function push($table, array $data) {
		bg::run([__CLASS__, 'syncPush'], [$table, $data]);
	}

	static public function syncPush($table, array $data) {
		\solutions\vertica\dbVertica::getConnect()->insert( $data, $table );
	}
}
