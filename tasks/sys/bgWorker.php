<?
declare(ticks = 1);
function sigHandler($signo) {
	switch ($signo) {
		case SIGTERM:
			print "Sigterm catched, terminating...";
			background::instance()->terminate();
		break;
	}
}
pcntl_signal(SIGTERM, "sigHandler");

$queue = cli::getArgument('queue');
$callback = bg::decodeCallback($queue);
background::instance()->listen($queue, function($data) use ($callback, $queue) {
	bg::setAlive($queue);
	call_user_func_array($callback, $data);
});
print "End";