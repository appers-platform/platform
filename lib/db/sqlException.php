<?
class sqlException extends \Exception {
	const CODE_DB_NF = 1;
	const CODE_TABLE_NF = 2;
	const CODE_FIELD_NF = 3;

	private $data;

	public function setData($data) {
		$this->data = $data;
	}

	public function getData() {
		return $this->data;
	}
}
