<?
class timer {
	private $is_run = false;
	private $time_start;
	private $time_total = 0;

	public function __construct($start = true) {
		if($start) $this->start();
	}

	public function start() {
		if($this->is_run)
			return;
		$this->time_start = microtime(true);
		$this->is_run = true;
	}

	public function stop() {
		if(!$this->is_run)
			return;
		$this->time_total += (microtime(true) - $this->time_start);
		$this->is_run = false;
	}

	public function isRun() {
		return $this->is_run;
	}

	public function reset() {
		$this->is_run = false;
		$this->time_start = 0;
		$this->time_total = 0;
	}

	public function getTime($round = false) {
		$result = $this->time_total;
		if($this->is_run) {
			$result += (microtime(true) - $this->time_start);
		}

		return $round ? round($result, $round) : $result;
	}

	public function __toString() {
		return (string) $this->getTime(4);
	}
}