<?
class context extends singleton {
	protected $variables = [];

	protected function __construct(array $variables = []) {
		$this->start($variables);
	}

	public function start(array $variables = []) {
		array_push($this->variables, $variables);
	}

	public function end() {
		array_pop($this->variables);
	}

	public function __get($name) {
		$k = count($this->variables) - 1;
		return isset($this->variables[$k][$name]) ? $this->variables[$k][$name] : null;
	}

	public function __set($name, $value) {
		$k = count($this->variables) - 1;
		$this->variables[$k][$name] = $value;
	}
}
