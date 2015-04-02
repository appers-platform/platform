<?
class cli {
	static protected $arguments = null;
	static protected $unique_task_id = null;
	static protected $was_new_line = true;
	static protected $current_command = null;

	static protected $output_to_log = false;
	static protected $with_wrap = false;

	static public function run(array $arguments = [], $with_wrap = true) {
		self::$with_wrap = $with_wrap;
		if($with_wrap) ob_start(['cli', 'out'], 1);
		if(!count($arguments)) {
			print "Available tasks:\n";
			self::printTaskList();
			if($with_wrap) ob_end_clean();
			return;
		}
		self::$current_command = implode(' ', $arguments);
		$task_name = array_shift($arguments);

		$map = self::getTasksMap();
		if(!$map[$task_name]) {
			print "Task '{$task_name}' not found.'";
			if($with_wrap) ob_end_clean();
			return;
		}

		/**
		 * @var task $task
		 */

		$timer = new timer();

		foreach($arguments as $argument) {
			if($char_pos = strpos($argument, '=')) {
				$name = substr($argument, 0, $char_pos);
				$value = substr($argument, $char_pos + 1);
				self::$arguments[$name] = $value;
			} else {
				self::$arguments[$argument] = '';
			}
		}

		if(self::hasArgument('toLog')) {
			self::$output_to_log = true;
		}

		self::$unique_task_id = getmypid();
		print 'Task "'.$task_name.'" was launched'.".\n";

		self::execute($map[$task_name]);
		
		print "\n";
		print 'Task "'.$task_name.'" was finished'.". Time: ".$timer." s.\n";
		if($with_wrap) ob_end_clean();
	}

	static protected function execute($filename) {
		require $filename;
	}

	static public function readLine() {
		return trim(fgets(STDIN));
	}

	static public function getArgument($name, $default = null) {
		return isset(self::$arguments[$name]) ? self::$arguments[$name] : $default;
	}

	static public function getArguments() {
		return self::$arguments;
	}

	static public function hasArgument($name) {
		return isset(self::$arguments[$name]);
	}

	static public function getLine() {
		return trim(fgets(STDIN));
	}

	static public function confirm($text, $default = true) {
		$condition = ($default ? 'Y/n' : 'y/N');
		do {
			print $text.' ['.$condition.'] ';
			$reply = strtolower(self::getLine());
		} while (!in_array($reply, ['', 'y', 'n']));

		if (!$reply) {
			print ($default ? 'y' : 'n')."\n";
			return $default;
		}

		return ($reply == 'y' ? true : false);
	}

	static public function out($buffer, $flags) {
		if(self::$output_to_log) {
			error_log('['.self::$unique_task_id.']:'."\t".' '.$buffer);
			return '';
		}

		$return = self::$was_new_line ? '['.self::$unique_task_id.' '.date('Y.m.d H:i:s').']:'."\t".' '.$buffer : $buffer;
		self::$was_new_line = (strpos($buffer, "\n") === (strlen($buffer) - 1));

		return $return;
	}

	static public function getTasksMap() {
		$list = [];

		$scan_map = [ ROOT => '', PROJECT_ROOT => '' ];
		foreach(scandir(ROOT.'/solutions') as $d) {
			if(in_array($d, ['.', '..'])) continue;
			$dir = ROOT.'/solutions/'.$d;
			$scan_map[$dir] = '::'.$d.'::';
		}

		foreach($scan_map as $dir => $prefix) {
			$file_info = new SplFileInfo($dir.'/tasks/');
			if(!$file_info->getRealPath())
				continue;
			$ri = new \RecursiveIteratorIterator(
				new \RecursiveDirectoryIterator($file_info->getRealPath()),
				\RecursiveIteratorIterator::SELF_FIRST
			);

			foreach ($ri as $file) {
				if ($file->getExtension() != 'php') {
					continue;
				}
				$name = $file->getBasename('.php');
				$path_name = substr(dirname($file->getPathname()), strlen($dir.'/tasks/'));
				$path_name = str_replace('/', '::', $path_name);
				if($path_name) $name = $path_name.'::'.$name;

				$list[$prefix.$name] = $file->getPathname();
			}
		}

		return $list;
	}

	static public function printTaskList() {
		foreach(array_keys(self::getTasksMap()) as $class) {
			print "\t".$class."\n";
		}
	}

	static public function runBackground($cmd) {
		return `{$cmd} > /dev/null 2>&1 &`;
	}

	static public function getTaskCmd($task_name, array $arguments = []) {
		$command = 'PROJECT='.PROJECT.' php '.EXEC_PATH.' '.$task_name.' ';
		foreach($arguments as $name => $value) {
			$command .= ($name.'='.$value.' ');
		}
		return $command;
	}

	static public function runBackgroundTask($task_name, array $arguments = [], $output = '/dev/null') {
		$command = self::getTaskCmd($task_name, $arguments);
		$command .= ' toLog > '.$output.' 2>&1 &';
		return `{$command}`;
	}

	static public function getCurrentCommand() {
		if ( PHP_SAPI != 'cli' ) return false;
		return EXEC_PATH.' '.self::$current_command;
	}

	static public function checkPid($pid) {
		$pid = (int) $pid;
		if(!$pid) return false;
		return (bool) (((int) `ps orss -p  {$pid} | wc -l`) - 1);
	}

	static public function runCmdWithPrint($cmd, $print_command = false) {
		$cmd = str_replace("\"", "\\\"", $cmd);
		$cmd = '/bin/sh -c "'.$cmd.'"';

		if($print_command)
			print $cmd."\n";

		$descriptorspec = array(
		   0 => array("pipe", "r"),   // stdin is a pipe that the child will read from
		   1 => array("pipe", "w"),   // stdout is a pipe that the child will write to
		   2 => array("pipe", "w")    // stderr is a pipe that the child will write to
		);
		flush();
		$process = proc_open($cmd, $descriptorspec, $pipes, realpath('./'), array());

		if (is_resource($process)) {
		    while (($s = fgets($pipes[1])) || ($e = fgets($pipes[2])) ) {
		        print $s;
		        print $e;
		        flush();
		    }
		}
	}
}
