<?
class log {
	static public function write($message, $level) {
		$current_log_level = LOG_LEVEL_ERROR;
		$prefix = '['.getmypid().'] ';
		if($caller = solutions::getRunnerSolution()) {
			$classname = 'solutions\\'.$caller;
			$current_log_level = $classname::getConfig('log_level', false, LOG_LEVEL_ERROR);
			$prefix = '('.$caller.') ';
		} else {
			$current_log_level = config::get('log_level', LOG_LEVEL_ERROR);
		}
		if($level <= $current_log_level) {
			error_log($prefix.$message);
		}
	}

	static public function debug($message) {
		return self::write($message, LOG_LEVEL_DEBUG);
	}

	static public function warning($message) {
		return self::write($message, LOG_LEVEL_WARNING);
	}

	static public function error($message) {
		return self::write($message, LOG_LEVEL_ERROR);
	}

	static public function fatal($message) {
		return self::write($message, LOG_LEVEL_FATAL);
	}

	static public function force($message) {
		return self::write($message, LOG_LEVEL_FATAL);
	}
}