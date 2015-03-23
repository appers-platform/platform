<?
class solutionController extends \solutions\solution {
	protected $output = false;
	protected $type = false;
	protected $meta = [];

	function post(){}
	function get(){}
	function first(){}
	function last(){}

	private $_values = [];
	protected $view = null;

	public function __get($name) {
		if(!isset($this->_values[$name]))
			return null;

		return $this->_values[$name];
	}

	public function __set($k, $v) {
		$this->_values[$k] = $v;
	}

	public function addMeta($name, $content) {
		$this->meta[] = [ 'name' => $name, 'content' => $content ];
	}

	public function addCustomMeta(array $params) {
		$this->meta[] = $params;
	}

	public function getMeta() {
		return $this->meta;
	}

	protected function returnJson($json) {
		$this->type = 'application/json';
		$this->output = json_encode($json);
	}

	public function render() {
		context::instance()->start();
		context::instance()->controller = $this;
		if(!is_file($this->getView()))
			return '';

		$this->output = $this->renderFile(
			$this->getView(),
			array_merge($this->_values, ['controller' => $this])
		);
		$this->type = DEFAULT_CONTENT_TYPE;
		context::instance()->end();
	}

	public function getOutput() {
		return $this->output;
	}

	public function getType() {
		return $this->type;
	}

	public function __toString() {
		try {
			$this->first();
			if(\request::isGET())
				$this->get();
			if(\request::isPOST())
				$this->post();
			$this->last();
			$this->render();
		} catch (Exception $e) {
			$this->output = application::echoException($e);
		}
		return (string) $this->output;
	}

	static public function getViewFileName($class_name) {
		$name = explode('\\', $class_name);
		array_shift($name);
		$solution_name = array_shift($name);
		$name = array_pop($name);
		$name = strlen($name) > 19 ? substr($name, 0, strlen($name) - 19) : $name;

		$custom_view = PROJECT_ROOT.'/solutions/'.$solution_name.'/'.$name.'.view.php';
		if(is_file($custom_view))
			return $custom_view;

		return ROOT.'/solutions/'.$solution_name.'/view/'.$name.'.view.php';
	}

	public function setView($view) {
		$this->view = $view;
	}

	public function getView() {
		if(get_called_class() == __CLASS__)
			throw new Exception('Direct call is denied.');

		if($this->view) {
			$name = explode('\\', get_called_class());
			array_shift($name);
			$solution_name = array_shift($name);
			return ROOT.'/solutions/'.$solution_name.'/view/'.$this->view.'.view.php';
		}

		return self::getViewFileName(get_called_class());
	}

	static public function getControllerFileName($class_name) {
		$name = explode('\\', $class_name);
		array_shift($name);
		$solution_name = array_shift($name);
		$name = array_pop($name);
		$name = strlen($name) > 19 ? substr($name, 0, strlen($name) - 19) : $name;

		return ROOT.'/solutions/'.$solution_name.'/controller/'.$name.'.php';
	}
}
