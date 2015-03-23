<?
interface kvStorage {
	static public function get($key);
	static public function set($name, $value, $ttl = 0);
	static public function inc($name, $value = 1);
	static public function dec($name, $value = 1);
	static public function delete($name);
}
