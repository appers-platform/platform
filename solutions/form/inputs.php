<?
namespace solutions\form;

abstract class input {
	protected $attrs = [];
	protected $value = '';
	protected $name = '';
	protected $title = '';

	public function __construct($name = null, $title = null) {
		$this->name = $name;
		$this->title = $title ?: $name;
	}

	public function setValue($value) {
		$this->value = $value;
		return $this;
	}

	public function setName($name) {
		$this->name = $name;
		if(!$this->title) $this->title = $name;
		return $this;
	}

	public function setTitle($title) {
		$this->title = $title;
		return $this;
	}

	public function setAttr($name, $value) {
		$this->attrs[$name] = $value;
		return $this;
	}

	public function setAttrs(array $attrs) {
		$this->attrs = array_merge($this->attrs, $attrs);
	}

	public function renderAttrs() {
		$result = [];
		foreach($this->attrs as $k => $v) {
			$result[] = $k.'="'.str_replace('"', '\\"', $v).'"';
		}
		return implode(' ', $result);
	}

	abstract public function draw();

	public function __toString() {
		ob_start();
		$this->draw();
		$result = ob_get_contents();
		ob_end_clean();
		return $result;
	}
}

class textarea extends input {
	public function draw() {
		?>
		<div class="form-group">
			<label for="exampleInputPassword1"><?=$this->title?></label>
			<textarea class="form-control" name="<?=$this->name?>"><?=$this->value?></textarea>
		</div>
	<?
	}
}

class text extends input {
	public function draw() {
		$this->setAttrs([
			'name'	=> $this->name,
			'value'	=> $this->value
		]);
?>
		<div class="form-group">
			<label for="exampleInputPassword1"><?=$this->title?></label>
			<input <?=$this->renderAttrs()?> type="text" class="form-control" placeholder="<?=$this->title?>">
		</div>
<?
	}
}

class password extends input {
	public function draw() {
		$this->setAttrs([
			'name'	=> $this->name,
			'value'	=> $this->value
		]);
		?>
		<div class="form-group">
			<label for="exampleInputPassword1"><?=$this->title?></label>
			<input <?=$this->renderAttrs()?> type="password" class="form-control" placeholder="<?=$this->title?>">
		</div>
	<?
	}
}

class hidden extends input {
	public function draw() {
		$this->setAttrs([
			'name'	=> $this->name,
			'value'	=> $this->value
		]);
		?><input <?=$this->renderAttrs()?> type="hidden"><?
	}
}

class select extends input {
	private $values = [];

	public function setValues(array $values, $force_keys = false) {
		if($force_keys || \helper::isAssoc($values)) {
			$this->values = $values;
		} else {
			foreach($values as $value) {
				$this->values[$value] = $value;
			}
		}
		return $this;
	}

	public function draw() {
		$this->setAttrs([ 'name' => $this->name ]);
		?>
		<div class="form-group">
			<label for="exampleInputPassword1"><?=$this->title?></label>
			<select <?=$this->renderAttrs()?> class="form-control">
				<? foreach($this->values as $k => $v) {
					?><option value="<?=str_replace('"', '\\"', $k)?>"><?=$v?></option><?
				} ?>
			</select>
		</div>
	<?
	}
}

class html extends input {
	public function __construct($html = null, $title = null) {
		$this->value = $html;
		parent::__construct('', $title);
	}

	public function setName($html) {
		$this->value = $html;
		parent::setName($html);
	}

	public function draw() {
		print $this->value;
	}
}
