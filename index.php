<?
try {
	define('E_REPORTING', E_ALL & ~E_NOTICE);
	error_reporting(E_REPORTING);
	ini_set('display_errors', 'On');
	define('TIME_START', microtime(true));
	define('ROOT', dirname(__FILE__));
	define('CONFIG_ROOT', dirname(dirname(__FILE__)).'/config');
	define('PROJECT', strtolower($_SERVER["HTTP_HOST"]));
	define('PROJECT_ROOT', dirname(ROOT).'/appers/'.PROJECT);
	if(!is_dir(PROJECT_ROOT)) {
		print '<html>
<head><title>404 Not Found</title></head>
<body bgcolor="white">
<center><h1>404 Not Found</h1></center>
<hr>
</body>
</html>
';
		exit;
	}
	define('PROJECTS_ROOT', dirname(ROOT).'/appers');
	define('EXEC_PATH', dirname(__FILE__).'/lib/exec.php');
	require ROOT . '/lib/application/loader.php';
	loader::init();
	config::init();
	if(config::get('restartWorkersOnChange')) {
		if(loader::isFilesChanged()) {
			bg::restartWorkers();
		}
	}
	i18n::setLocale($_SERVER['A_LANGUAGE'] ?: config::get('defaultLocale'));
	application::run($_SERVER['REQUEST_URI']);
} catch (Exception $e) {
	print '<pre style="position:absolute; word-wrap: break-word; top: 0; left: 0; width: 100%;">';
	print 'Exception ['.$e->getCode().']: ';
	print $e->getMessage();
	print "\n#  ";
	print $e->getFile();
	print "(";
	print $e->getLine();
	print ")\n";
	print_r($e->getTraceAsString());
	print '</pre>';
}
