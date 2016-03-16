<?
class solutionLoaderController extends controller {
	public function render() {
		$this->type = 'text/html; charset=utf-8';
		$content = $this->content;

		if($layout = $this->solution->getConfig('layout')) {
			$this->layout = $layout;
		}

		if(request::getInt('_frame')) {
			$this->wrappers_enabled = false;
			$this->layout = 'frame';
			$this->in_frame = true;
		}

		if($this->wrappers_enabled && is_file($wrapper_file = $this->getControllerWrapper())) {
			$content = $this->renderFile($wrapper_file, [ 'content' => $content ]);
		}
		if($this->wrappers_enabled && is_file($wrapper_file = $this->getWrapper())) {
			$content = $this->renderFile($wrapper_file, [ 'content' => $content ]);
		}

		\solutions\solution::finalizationRendering();

		$this->output = $this->renderFile(
			renderer::getLayoutPath($this->layout),
			[ 'content' => $content ]
		);
	}

	public function getDefaultTitle() {
		$path = explode('_', get_class($this));
		$path = array_slice($path, 3, 2);

		$title = [];
		while($name = array_shift($path)) {
			if($name != 'index')
				$title[] = ucfirst($name);
		}

		if($title)
			return implode(" - ", $title);

		return PROJECT;
	}

	public function first() {
		$class = get_class($this);
		$class = explode('_', substr($class, 12, strlen($class) - 23));
		$solution_name = array_shift($class);
		$controller_name = implode('_', $class);
		$this->solution = solutions::controller($solution_name, $controller_name);
		context::instance()->start();
		context::instance()->controller = $this->solution;

		event::fire('beforeControllerRun');
		$this->solution->first();
		if(\request::isGET())
			$this->solution->get();
		if(\request::isPOST())
			$this->solution->post();
		$this->solution->last();

		event::fire('beforeControllerRender');
		$this->solution->render();

		$this->content = $this->solution->getOutput();

		if($this->solution->getType() != DEFAULT_CONTENT_TYPE) {
			response::setHeader('Content-Type: '.$this->solution->getType(), null);
			response::send($this->content);
			exit;
		}

		styles::scan($this->getViewPath(), styles::GROUP_SOLUTIONS, $this->getLastName());
		styles::scan($this->getViewPath(), styles::GROUP_SOLUTIONS, 'styles');

		styles::scan($this->getOverrideViewPath(), styles::GROUP_SOLUTIONS, $this->getLastName());
		styles::scan($this->getOverrideViewPath(), styles::GROUP_SOLUTIONS, 'styles');

		js::scan($this->getViewPath(), js::GROUP_SOLUTIONS, $this->getLastName());
		js::scan($this->getViewPath(), js::GROUP_SOLUTIONS, 'scripts');

		if(!$this->title)
			$this->title = $this->getDefaultTitle();

		context::instance()->end();
	}

	public function getWrapper() {
		$path = get_class($this);
		$path = substr($path, 2, strlen($path) - 13);
		$path = explode('_', $path);
		array_pop($path);
		$path[] = '_wrapper.php';

		if(is_file($filename = PROJECT_ROOT.'/'.implode('/', $path)))
			return $filename;

		array_pop($path);
		$path[] = 'view';
		$path[] = '_wrapper.php';

		return ROOT.'/'.implode('/', $path);
	}

	public function getControllerWrapper() {
		$path = get_class($this);
		$path = substr($path, 2, strlen($path) - 13);
		$path = explode('_', $path);
		$file = array_pop($path);
		$path[] = '_wrapper_'.$file.'.view.php';

		if(is_file($filename = PROJECT_ROOT.'/'.implode('/', $path)))
			return $filename;

		array_pop($path);
		$path[] = 'view';
		$path[] = '_wrapper_'.$file.'.view.php';

		return ROOT.'/'.implode('/', $path);
	}

	public function getViewPath() {
		$path = get_class($this);
		$path = substr($path, 2, strlen($path) - 13);
		$path = explode('_', $path);
		array_pop($path);
		array_push($path, 'view');
		return ROOT.'/'.implode('/', $path);
	}

	public function getOverrideViewPath() {
		$path = get_class($this);
		$path = substr($path, 2, strlen($path) - 13);
		$path = explode('_', $path);
		array_pop($path);
		$solution = array_pop($path);
		array_push($path, $solution);
		return PROJECT_ROOT.'/'.implode('/', $path);
	}
}