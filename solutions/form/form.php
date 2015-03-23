<?
namespace solutions;

use solutions\form\text;
use solutions\form\password;
use solutions\form\select;
use solutions\form\input;
use solutions\form\textarea;
use solutions\form\hidden;

class form extends solution {
	private $action;
	private $method;
	private $fields;
	private $last_fields;
	private $header = '';
	private $message = '';
	private $class = '';
	private $send_button_name = 'Submit';
	private $cancel_button_name = null;
	private $cancel_button_href = null;
	private $id;

	public function __construct($header = null, $action = null, $method = 'GET') {
		$this->action = $action;
		$this->method = $method;
		$this->header = $header;
		$this->fields = [];
		$this->last_fields = [];
	}

	public function setMessage($message) {
		$this->message = $message;
		return $this;
	}

	public function setId($id) {
		$this->id = $id;
		return $id;
	}

	public function getMessage() {
		return $this->message;
	}

	public function setSendButtonName($name) {
		$this->send_button_name = $name;
		return $this;
	}

	public function setCancelButton($name, $href = '..') {
		$this->cancel_button_name = $name;
		$this->cancel_button_href = $href;
		return $this;
	}

	public function setClass($class) {
		$this->class = $class;
		return $this;
	}

	public function setHeader($header) {
		$this->header = $header;
		return $this;
	}

	public static function create(array $fields, $header = null, $action = null, $method = 'GET', array $last_fields = []) {
		$form = new self($header, $action, $method);
		foreach($fields as $field) {
			$form->addField($field);
		}
		foreach($last_fields as $field) {
			$form->addField($field, true);
		}

		return $form;
	}

	public function addField($data, $last = false) {
		if($data instanceof input) {
			if($last) {
				$this->last_fields[] = $data;
			} else {
				$this->fields[] = $data;
			}
		} else {
			if(!is_array($data)) {
				$data = ['name' => $data];
			}
			if(!\helper::isAssoc($data)) {
				$data = [
					'name'	=> isset($data[0]) ? $data[0] : null,
					'type'	=> isset($data[1]) ? $data[1] : null,
					'value'	=> isset($data[2]) ? $data[2] : null,
				];
			}

			switch(isset($data['type']) ? $data['type'] : null) {
				case 'password':
					$field = new password();
					break;
				case 'select':
					$field = new select();
					break;
				case 'textarea':
					$field = new textarea();
					break;
				case 'hidden':
					$field = new hidden();
					break;
				default:
					$field = new text();
					break;
			}


			foreach($data as $k => $v) {
				if(is_callable([$field, $method = 'set'.ucfirst($k)])) {
					$field->$method($v);
				}
			}

			if($last) {
				$this->last_fields[] = $field;
			} else {
				$this->fields[] = $field;
			}
		}
	}

	public function __toString() {
		ob_start();
		$this->draw();
		$result = ob_get_contents();
		ob_end_clean();
		return $result;
	}

	public function draw() {
	?>
<form
	role="form"
	class="solution form <?=$this->class?>"
	method="<?=$this->method?>"
	action="<?=$this->action?>"
	id="<?=$this->id?>"
	>
	<fieldset>
		<? if($this->header) { ?>
			<h4 class="header"><?=$this->header?></h4>
		<? } ?>
		<? if($this->message) { ?>
			<h5 class="message"><?=$this->message?></h5>
		<? } ?>
		<? foreach($this->fields as $field) print $field; ?>
		<button type="submit" class="btn btn-default"><?=$this->send_button_name?></button>
		<? if($this->cancel_button_name){ ?>
			<a class="btn btn-default btn-cancel" href="<?=$this->cancel_button_href?>"><?=$this->cancel_button_name?></a>
		<? } ?>
		<? foreach($this->last_fields as $field) print $field; ?>
	</fieldset>
</form>
	<?
	}
}


