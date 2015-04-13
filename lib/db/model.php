<?
abstract class model {
	const db_PK = 'id';
	const db_table = null;
	const db_connection = null;
	const db_class = 'dbMysql';

	private $_id;
	private $_loaded = false;
	private $_modified_fields = [];
	private $_values = [];

	static private $_loaded_elements = [];

	public function __construct($id = 0) {
		$this->_id = $id;
		self::$_loaded_elements[get_called_class()] = [];
	}

	private function _load() {
		if($this->_loaded) return;
		if(!$this->_id) return;
		$this->_loaded = true;

		if(!$this->_loadCache()) {
			$connector = static::db_class;
			$this->_values = $connector::getConnect(static::db_connection)->
				getRow([static::db_PK => $this->_id], static::getTable());
			$this->_saveCache();
		}

		if(!isset(self::$_loaded_elements[get_called_class()][$this->_id]))
			self::$_loaded_elements[get_called_class()][$this->_id] = [];
		self::$_loaded_elements[get_called_class()][$this->_id][] = $this;

		$this->_id = $this->_values[$this->getPK()];
	}

	private function _loadCache() {
		$cache = mCache::get('MD_'.get_called_class().$this->_id);
		if(is_array($cache)) {
			$this->_values = $cache;
			return true;
		}
		return false;
	}

	private function _saveCache() {
		if(!$this->_id)
			return false;

		if(!$element->_loaded)
			return false;

		mCache::set('MD_'.get_called_class().$this->_id, $this->_values);
		return true;
	}

	private function _deleteCache() {
		if(!$this->_id)
			return false;

		mCache::delete('MD_'.get_called_class().$this->_id, $this->_values);
		return true;
	}

	static public function getPK() {
		return static::db_PK;
	}

	public function getNow() {
		$connector = static::db_class;
		return $connector::getConnect(static::db_connection)->getNow();
	}

	static public function getTable() {
		if(static::db_table)
			return static::db_table;
		$class = strlen(get_called_class()) > 5 ?
			substr(get_called_class(), 0, strlen(get_called_class()) - 5) : get_called_class();
		return str_replace('\\', '_', $class);
	}

	public function save() {
		if(!$this->_id)
			throw new Exception('Model is not associated with row');
		$data = [];
		foreach($this->_modified_fields as $field) {
			$data[$field] = $this->_values[$field];
		}
		$connector = static::db_class;
		$connector::getConnect(static::db_connection)->update(
			$data,
			[static::db_PK => $this->_id],
			static::getTable()
		);

		$this->_saveCache();

		if(isset(self::$_loaded_elements[get_called_class()][$this->_id])) {
			if(is_array(self::$_loaded_elements[get_called_class()][$this->_id])) {
				foreach(self::$_loaded_elements[get_called_class()][$this->_id] as $element) {
					$element->_modified_fields = [];
					$element->_loaded = true;
				}
			}
		}
	}

	public function insert() {
		if($this->_id) {
			throw new Exception("Model already associated with row");
		}

		$connector = static::db_class;
		$this->_id = $connector::getConnect(static::db_connection)->
			insert(array_merge($this->_values, [static::db_PK => 0]), static::getTable());
		$this->_loaded = true;

		if(!isset(self::$_loaded_elements[get_called_class()][$this->_id]))
			self::$_loaded_elements[get_called_class()][$this->_id] = [];
		self::$_loaded_elements[get_called_class()][$this->_id][] = $this;

		$this->_saveCache();

		return $this->_id;
	}

	static public function modelWhere(array $params) {
		if(get_called_class() == __CLASS__)
			throw new Exception('Direct call of method is denied.');
		$class_name = get_called_class();

		if($model = $class_name::getWhere($params)) {
			$model = $model[0];
		} else {
			$pk = 0;
			foreach($params as $name => $value) {
				if($class_name::db_PK == $name) {
					$pk = $value;
					unset($params[$name]);
					break;
				}
			}

			$model = new $class_name($pk);
			foreach($params as $name => $value) {
				$model->$name = $value;
			}
		}

		return $model;
	}

	public function store() {
		if($this->getPrimaryId()) {
			$this->save();
		} else {
			$this->insert();
		}
		return $this->getPrimaryId();
	}

	public function __get($field) {
		$this->_load();
		$result = isset($this->_values[$field]) ? $this->_values[$field] : null;
		if(is_callable([$this, '__get_'.$field])) {
			$result = call_user_func([$this, '__get_'.$field], $result);
		}
		return $result;
	}

	public function __set($field, $value) {
		$this->_values[$field] = $value;
		if(!in_array($field, $this->_modified_fields))
			$this->_modified_fields[] = $field;
	}

	public function getPrimaryId() {
		return $this->_id;
	}

	public function getId() {
		return $this->_id;
	}

	public function delete() {
		$connector = static::db_class;
		$connector::getConnect(static::db_connection)->
			delete([static::db_PK => $this->_id], static::getTable());

		$this->_deleteCache();

		if(is_array(self::$_loaded_elements[get_called_class()][$this->_id])) {
			foreach(self::$_loaded_elements[get_called_class()][$this->_id] as $element) {
				$element->_id = 0;
				$element->_values = [];
				$element->_loaded = false;
				$element->_modified_fields = [];
			}
		}

		$this->_id = 0;
		$this->_values = [];
		$this->_loaded = false;
		$this->_modified_fields = [];
	}

	/**
	 * @param $params_where_sql
	 * @param null $sql_params
	 * @param int $order
	 * @param int $limit
	 * @return array
	 * @throws Exception
	 */
	static public function getWhere($params_where_sql, $sql_params = null, $order = null, $limit = 0) {
		if(get_called_class() == __CLASS__)
			throw new Exception('Direct call of method is denied.');

		if(is_array($params_where_sql)) {
			$table = static::getTable();
		} else {
			$table = $sql_params;
		}

		$connector = static::db_class;
		$ids = $connector::getConnect(static::db_connection)
			->getCols($params_where_sql, $table, static::db_PK, $order, $limit);

		$result = [];
		foreach($ids as $id) {
			$result[] = new static($id);
		}

		return $result;
	}

	static public function get($id = 0) {
		$class_name = get_called_class();
		return new $class_name($id);
	}

	static public function getOneWhere($params_where_sql, $sql_params = null, $order = null, $limit = 0) {
		$result = self::getWhere($params_where_sql, $sql_params, $order, $limit);
		if(!$result) return null;
		return $result[0];
	}

	/**
	 * @return dbSql
	 * @throws Exception
	 */
	static public function getConnect() {
		if(get_called_class() == __CLASS__)
			throw new Exception('Direct call of method is denied.');

		$connector = static::db_class;
		return $connector::getConnect(static::db_connection);
	}

	public function find($order = null) {
		if($this->_loaded) {
			throw new Exception('Method can be called only for unloaded object');
		}

		/*
		if(!$this->_values) {
			throw new Exception('You have to specify at least one field');
		}
		*/

		if(!$order) {
			$order = [static::getPK(), 'ASC'];
		} else if(is_string($order) && in_array(trim(strtoupper($order)), ['ASC', 'DESC'])) {
			$order = [static::getPK(), trim(strtoupper($order))];
		}

		$ids = static::getConnect()->getCols(
			$this->_values,
			static::getTable(),
			static::getPK(),
			$order,
			1
		);

		if(!$ids)
			return false;

		$this->_id = $ids[0];
		$this->_values = false;

		return true;
	}

	public function load($order = null) {
		return $this->find($order);
	}

	public function findAll($order = null, $limit = null) {
		if($this->_loaded) {
			throw new Exception('Method can be called only for unloaded object');
		}

		if(!$order) {
			$order = [self::getPK(), 'ASC'];
		} else if(is_string($order) && in_array(trim(strtoupper($order)), ['ASC', 'DESC'])) {
			$order = [self::getPK(), trim(strtoupper($order))];
		}

		$ids = self::getConnect()->getCols(
			$this->_values,
			self::getTable(),
			self::getPK(),
			$order,
			$limit
		);

		if(!$ids)
			return [];

		$result = [];
		foreach($ids as $id)
			$result[] = new static($id);

		return $result;
	}
}
