<?
class controller extends renderer {
	protected $layout = 'default';
	protected $output = false;
	protected $type = false;
	protected $meta = [];
	public $title;
	protected $wrappers_enabled = true;

	function post(){}
	function get(){}
	function first(){}
	function last(){}

	public function returnJson($json) {
		$this->type = 'application/json';
		$this->output = json_encode($json);
	}

	public function returnPlain($output) {
		$this->output = $output;
	}

	public function setOutput($output) {
		return $this->output = $output;
	}

	public function setTitle($title) {
		$this->title = $title;
	}

	public function getTitle() {
		return $this->title;
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

	public function render() {
		$content = $this->renderFile(PROJECT_ROOT.$this->getView(), $this, true);

		if(request::getInt('_frame')) {
			$this->wrappers_enabled = false;
			$this->layout = 'frame';
			$this->in_frame = true;
		}

		if($this->wrappers_enabled && is_file($wrapper_file = PROJECT_ROOT.$this->getWrapper())) {
			$content = $this->renderFile($wrapper_file, [ 'content' => $content ]);
		}

		\solutions\solution::finalizationRendering();

		$this->output = $this->renderFile(
			renderer::getLayoutPath($this->layout),
			[ 'content' => $content ]
		);
		$this->type = DEFAULT_CONTENT_TYPE;
	}

	public function setLayout($layout) {
		$this->layout = $layout;
	}

	public function getView() {
		$path = explode('_', get_class($this));
		array_pop($path);
		$view = array_pop($path);
		array_push($path, '_view');
		array_push($path, $view.'.view.php');
		return '/controller/'.implode('/', $path);
	}

	public function getWrapper() {
		$path = explode('_', get_class($this));
		array_pop($path);
		array_pop($path);
		array_push($path, '_view');
		array_push($path, '_wrapper.php');
		return '/controller/'.implode('/', $path);
	}

	public function getViewPath() {
		$path = explode('_', get_class($this));
		array_pop($path);
		array_pop($path);
		array_push($path, '_view');
		return '/controller/'.implode('/', $path);
	}

	static public function getViewFileName($class_name) {
		$path = explode('_', $class_name);
		array_pop($path);
		$name = array_pop($path);
		array_push($path, '_view');
		array_push($path, $name);
		return PROJECT_ROOT.'/controller/'.implode('/', $path).'.view.php';
	}

	static public function getControllerFileName($class_name) {
		$path = explode('_', $class_name);
		array_pop($path);
		return PROJECT_ROOT.'/controller/'.implode('/', $path).'.php';
	}

	public function getLastName() {
		$path = explode('_', get_class($this));
		array_pop($path);
		return array_pop($path);
	}

	public function getCSSClass() {
		$class = substr($s = get_class($this), 0, strlen($s) - strlen('_controller'));
		if($this->in_frame) {
			$class .= ' in_frame';
		}
		return $class;
	}

	public function getDefaultTitle() {
		$path = explode('_', get_class($this));
		array_pop($path);
		$title = [];
		while($name = array_shift($path)) {
			if($name != 'index')
				$title[] = ucfirst($name);
		}

		if($title)
			return implode(" - ", $title);
		return PROJECT;
	}

	public function getFirstName() {
		$path = explode('_', get_class($this));
		return array_shift($path);
	}

	public function getOutput() {
		return $this->output;
	}

	public function getType() {
		return $this->type;
	}

	public function disableWrappers() {
		$this->wrappers_enabled = false;
	}

	public function enableWrappers() {
		$this->wrappers_enabled = true;
	}

	public function isWrappersEnabled() {
		return $this->wrappers_enabled;
	}
}
