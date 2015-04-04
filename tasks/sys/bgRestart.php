<?
$pid_file = ROOT.'/cache/'.PROJECT.'-sys_bg.pid';
if(is_file($pid_file) && cli::checkPid($pid = file_get_contents($pid_file))) {
	print "Terminating workers.";
	posix_kill($pid, SIGTERM);
	$i = 0;
	while (cli::checkPid($pid) && $i++ < 30) {
		sleep(1);
		print '.';
	}
	if($i >= 30) {
		print "Error\n";
	} else {
		print "Done\n";
		cli::runBackgroundTask('sys::bg', []);
	}
}
