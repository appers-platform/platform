<?
class mock extends stdClass {

	public function __call($method, $args) {
		return null;
	}

	public function __toString() {
		return '';
	}

}
