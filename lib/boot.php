<?
define('E_REPORTING', E_ALL & ~E_NOTICE);
error_reporting(E_REPORTING);
ini_set('display_errors', 'On');
define('TIME_START', microtime(true));
define('ROOT', dirname(dirname(__FILE__)));
define('CONFIG_ROOT', dirname(dirname(dirname(__FILE__))).'/config');
define('PROJECT', strtolower(isset($_ENV["PROJECT"]) ? $_ENV["PROJECT"] : null));
define('PROJECT_ROOT', PROJECT ? dirname(ROOT).'/appers/'.PROJECT : null);
define('PROJECTS_ROOT', dirname(ROOT).'/appers');
define('EXEC_PATH', dirname(__FILE__).'/exec.php');
require ROOT.'/lib/application/loader.php';
loader::init();
config::init();
if(config::get('restartWorkersOnChange')) {
	if(loader::isFilesChanged()) {
		bg::restartWorkers();
	}
}
