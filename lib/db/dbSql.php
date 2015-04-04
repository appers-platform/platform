<?
abstract class dbSql {
	/**
	 * @var timer timer
	 */
	protected $timer;

	static private $connections = [];
	private $_cache = [];
	protected $config_name = null;

	protected $splitter = '`';

	/**
	 * @param string $sql
	 * @param array $params
	 * @return PDOStatement
	 */
	abstract public function directQuery($sql, array $params = [], $autocreate = true);

	abstract public function getLastInsertId();

	abstract public function createDB($db_name);
	abstract public function selectDB($db_name);
	abstract public function createTable($table, $id_name);
	abstract public function addField($table, $field_name);

	abstract public function getNow();

	abstract protected function __construct($config_name, $without_select_db = false);

	public function __get($k) {
		if($k == 'splitter') {
			return $this->splitter;
		}
		throw new Exception('Access to undefined property');
	}

	/**
	 * @param string $config_name
	 * @return dbSql
	 * @throws Exception
	 */
	static public function getConnect($config_name = '') {
		$class = get_called_class();
		if($class == __CLASS__)
			throw new Exception('Class dbSql is abstract.');
		if(!isset($connections[$config_name])) {
			try {
				$connections[$config_name] = new $class($config_name);
			} catch (sqlException $e) {
				if($e->getCode() == sqlException::CODE_DB_NF) {
					$connections[$config_name] = new $class($config_name, true);
					$connections[$config_name]->createDB($e->getData());
					$connections[$config_name]->selectDB($e->getData());
				}
			}
		}
		return $connections[$config_name];
	}

	public function query($sql, array $params = [], $autocreate = true) {
		try {
			return $this->directQuery($sql, $params, $autocreate);
		} catch (sqlException $e) {
			if(!$autocreate)
				throw $e;

			if($e->getCode() == sqlException::CODE_TABLE_NF) {
				$this->createTable($e->getData(), 'id');
				return $this->query($sql, $params);
			}

			if($e->getCode() == sqlException::CODE_FIELD_NF) {
				list($table, $field) = $e->getData();
				$this->addField($table, $field);
				return $this->query($sql, $params);
			}

			return $this->directQuery($sql, $params);
		}
	}

	protected function cache($k, $v = null) {
		if($v == null)
			return isset($this->_cache[$k]) ?
				$this->_cache[$k] : null;
		$this->_cache[$k] = $v;
	}

	public function getRow( $params_sql, $table = null ) {
		if ( is_array($params_sql) ) {
			$values = [];
			$keys = [];
			foreach ( $params_sql as $k => $v ) {
				if ( is_null($v) ) {
					$keys[] = "`{$k}` IS NULL";
				} else {
					if(is_array($v)) {
						$keys[] = $this->splitter.$k.$this->splitter." {$v[0]} :{$k}";
						$values[":{$k}"] = $v[1];
					} else {
						$keys[] = $this->splitter.$k.$this->splitter." = :{$k}";
						$values[":{$k}"] = $v;
					}
				}
			}

			$sql = "SELECT * FROM {$table} WHERE " . implode($keys, ' AND ') . " LIMIT 1";
		} else {
			$sql = $params_sql;
			$values = $table;
		}

		$data = $this->query($sql, $values)->fetch(PDO::FETCH_ASSOC);
		if ( !$data ) $data = null;
		return $data;
	}

	public function getRows( $params_sql, $table = null) {
		if ( is_array($params_sql) ) {
			$values = [];
			$keys = [];
			foreach ( $params_sql as $k => $v ) {
				if ( is_null($v) ) {
					$keys[] = $this->splitter.$k.$this->splitter." IS NULL";
				} else {
					if(is_array($v)) {
						$keys[] = $this->splitter.$k.$this->splitter." {$v[0]} :{$k}";
						$values[":{$k}"] = $v[1];
					} else {
						$keys[] = $this->splitter.$k.$this->splitter." = :{$k}";
						$values[":{$k}"] = $v;
					}
				}
			}

			$sql = "SELECT * FROM {$table} " . ( $keys ? " WHERE " . implode($keys, ' AND ') : '' );
		} else {
			$sql = $params_sql;
			$values = is_array($table) ? $table : [];
		}

		return $this->query($sql, $values)->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 * @param $params_sql
	 * @param null $table
	 * @param string $column
	 * @param int $limit
	 * @return array
	 */
	public function getCols( $params_sql, $table = null, $column = '*', $order = null, $limit = 0)
	{
		if ( is_array($params_sql) ) {
			$values = [];
			foreach ( $params_sql as $k => $v ) {
				if ( is_null($v) ) {
					$keys[] = $this->splitter.$k.$this->splitter." IS NULL";
				} else {
					if(is_array($v)) {
						$keys[] = $this->splitter.$k.$this->splitter." {$v[0]} :{$k}";
						$values[":{$k}"] = $v[1];
					} else {
						$keys[] = $this->splitter.$k.$this->splitter." = :{$k}";
						$values[":{$k}"] = $v;
					}
				}
			}

			$sql = "SELECT {$column} FROM {$table} " . ( $keys ? " WHERE " . implode($keys, ' AND ') : '' );
			if($order) {
				if(is_array($order)) {
					$sql .= ' ORDER BY '.implode(' ', $order).' ';
				} else if(!strpos($order, "\t") && !strpos($order, " ")) {
					$sql .= ' ORDER BY '.$order.' ASC ';
				} else {
					$sql .= ' ORDER BY '.$order.' ';
				}
			}
			if($limit) $sql .= ' LIMIT '.((int)$limit);
		} else {
			$sql = $params_sql;
			$values = $table;
		}

		if($values === null) $values = [];

		return $this->query($sql, $values)->fetchAll(PDO::FETCH_COLUMN);
	}

	public function getScalar( $params_sql, $table = null, $column = '*', $order = null ) {
		$ids = $this->getCols( $params_sql, $table, $column, $order, 1);
		if(!count($ids)) return null;
		return $ids[0];
	}

	public function insert( $data, $table, $replace = false ) {
		$values = [];
		$keys = [];
		foreach ( $data as $k => $v ) {
			$keys[] = ":{$k}";
			$values[":{$k}"] = $v;
		}

		$sql = ( $replace ? 'REPLACE' : 'INSERT' ) . " INTO {$table}(".$this->splitter . implode($this->splitter.', '.$this->splitter, array_keys($data)) . $this->splitter .") VALUES(" . implode(', ', $keys) . ')';

		$this->query($sql, $values);

		return $this->getLastInsertId();
	}

	public function insertUpdate( $insert, $update, $table ) {
		$values = [];
		$insert_keys = [];
		$update_keys = [];
		foreach ( $insert as $k => $v ) {
			if ( strpos($v, 'SQL:') === 0 ) {
				$insert_keys[] = $this->splitter.$k.$this->splitter." = " . str_replace('SQL:', '', $v);
			} else {
				$insert_keys[] = $this->splitter.$k.$this->splitter." = :{$k}";
				$values[":{$k}"] = $v;
			}
		}

		foreach ( $update as $k => $v ) {
			if ( strpos($v, 'SQL:') === 0 ) {
				$update_keys[] = $this->splitter.$k.$this->splitter." = " . str_replace('SQL:', '', $v);
			} else {
				$update_keys[] = $this->splitter.$k.$this->splitter." = :{$k}";
				$values[":{$k}"] = $v;
			}
		}
		$sql = "INSERT INTO {$table} SET " . implode(', ', $insert_keys) . " ON DUPLICATE KEY UPDATE " . implode(', ', $update_keys);
		return $this->query($sql, $values);
	}

	public function update( $data, $params, $table ) {
		$values = [];
		$keys = [];
		foreach ( $data as $k => $v ) {
			if ( strpos($v, 'SQL:') === 0 ) {
				$keys[] = $this->splitter.$k.$this->splitter." = " . str_replace('SQL:', '', $v);
			} else {
				$keys[] = $this->splitter.$k.$this->splitter." = :d_{$k}";
				$values[":d_{$k}"] = $v;
			}
		}

		$pars = [];
		foreach ( $params as $k => $v ) {
			if ( is_null($v) ) {
				$pars[] = $this->splitter.$k.$this->splitter." IS NULL";
			} else {
				if(is_array($v)) {
					$pars[] = $this->splitter.$k.$this->splitter." {$v[0]} :p_{$k}";
					$values[":p_{$k}"] = $v[1];
				} else {
					$pars[] = $this->splitter.$k.$this->splitter." = :p_{$k}";
					$values[":p_{$k}"] = $v;
				}
			}
		}

		$sql = "UPDATE {$table} SET " . implode($keys, ', ') . ' WHERE ' . implode($pars, ' AND ');
		return $this->query($sql, $values);
	}

	public function delete( $data, $table, $single = false ) {
		$keys = [];
		$values = [];
		foreach ( $data as $k => $v ) {
			if ( strpos($v, 'SQL:') === 0 ) {
				$keys[] = $this->splitter.$k.$this->splitter." = " . str_replace('SQL:', '', $v);
			} else {
				$keys[] = $this->splitter.$k.$this->splitter." = :d_{$k}";
				$values[":d_{$k}"] = $v;
			}
		}

		$sql = "DELETE FROM {$table} WHERE " . implode($keys, ' AND ') .
			( $single ? ' LIMIT 1' : '' );

		return $this->query($sql, $values);
	}

	public function getTime($round = 2) {
		return $this->timer->getTime($round);
	}
}
