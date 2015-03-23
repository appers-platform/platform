<?
class memoryLimitException extends Exception {
	static public function check() {
		$used = application::getMemoryUsage();
		$max_available = application::getMemoryLimit();
		if($used > round($max_available*0.9)) {
			throw new self("Memory limit exception - used {$used} of {$max_available}MB");
		}
	}
}
