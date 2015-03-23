<?
// RUNNING
if(!PROJECT) {
	print "Can't run without of project.\n";
	return;
}

$pid_file = ROOT.'/cache/'.PROJECT.'-sys_bg.pid';
if(is_file($pid_file) && cli::checkPid(file_get_contents($pid_file))) {
	print "Already runned\n";
	return;
}
file_put_contents($pid_file, getmypid());

global $sigterm_catced;
global $workers;
global $pipes;

$sigterm_catced = false;
$workers = [];
$pipes = [];
declare(ticks = 1);
function sigHandler($signo) {
	switch ($signo) {
		case SIGTERM:
			global $sigterm_catced;
			$sigterm_catced = true;
		break;
	}
}
pcntl_signal(SIGTERM, "sigHandler");

// WORK

$ttl = config::get('bg')['manager_ttl'] ?: 60;

$last_active = time();

while(1) {
	$actual = 0;
#	print "loop\n";
	foreach(bg::getAll() as $queue) {
#		print "Checking {$queue}...";
		if(bg::checkAlive($queue) && !$sigterm_catced) {
#			print "alive...";
			$actual++;
			if(!$workers[$queue] || !proc_get_status($workers[$queue])['running']) {
				$workers[$queue] = proc_open(cli::getTaskCmd('sys::bgWorker', ['queue' => $queue, 'toLog' => '']), [
					0 => array('pipe', 'r'),
					1 => array('pipe', 'w'),
					2 => array('pipe', 'w')
				], $pipes[$queue], null, $_ENV);

				print "Run worker '{$queue}'\n";
			} else {
#				print "OK";
			}
		} else {
			print "Terminating worker '{$queue}'...";
			if(proc_get_status($workers[$queue])['running'])
				proc_terminate($workers[$queue], SIGTERM);
			unset($workers[$queue]);
			unset($pipes[$queue]);
			print "Terminated worker '{$queue}'\n";
		}
	}

	if($sigterm_catced) {
		exit;
	}

	if($actual) {
		$last_active = time();
	} else {
		if($last_active < time() - $ttl) {
			print "No activity last time, terminated master-process.";
			return;
		}
	}

	sleep(5);
}

