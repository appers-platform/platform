<?
abstract class TModel extends model {
	public function save() {
		$this->update_dt = $this->getNow();
		parent::save();
	}

	public function insert() {
		$this->create_dt = $this->getNow();
		parent::insert();
	}

	static public function getTable() {
		if(static::db_table)
			return static::db_table;
		$class = strlen(get_called_class()) > 6 ?
			substr(get_called_class(), 0, strlen(get_called_class()) - 6) : get_called_class();
		return str_replace('\\', '_', $class);
	}
}
