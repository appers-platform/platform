<?
namespace solutions\utrack;
interface storageDriver {
	static public function push($table, array $data);
	static public function syncPush($table, array $data);
	static public function query($query);
	static public function getNow();
	static public function getRows( $params_sql, $table = null );
}