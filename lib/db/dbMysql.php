<?
class dbMysql extends dbSql {
	/**
	 * @var PDO $connection
	 */
	protected $connection;

	/**
	 * @param string $sql
	 * @param array $params
	 * @return PDOStatement
	 * @throws Exception
	 */
	public function directQuery($sql, array $params = [], $autocreate = true) {
		$query = $this->connection->prepare($sql);
		$this->timer->start();

		try {
			$result = $query->execute($params);
			#print $sql; print_r($params);
		} catch ( PDOException $e ) {
			if(!$autocreate) {
				throw $e;
			}

			if($e->getCode() == '42S02') {
				$matches = [];
				preg_match("/Table '[^\\.]+\\.([^\\']+)' doesn't exist/", $e->getMessage(), $matches);
				if(isset($matches[1]) && $matches[1]) {
					$e = new sqlException($e->getMessage(), sqlException::CODE_TABLE_NF, $e);
					$e->setData($matches[1]);
				}
				throw $e;
			}
			if($e->getCode() == '42S22') {
				$matches = [];
				preg_match("/Unknown column '([^\\']+)' in /", $e->getMessage(), $matches);
				$field = isset($matches[1]) ? $matches[1] : null;

				$matches = [];
				preg_match("/^insert( |\\t)+into( |\\t)+([^ \\t\\(]+)/i", $sql, $matches);
				if(isset($matches[3]) && $matches[3]) {
					$table = $matches[3];
				} else {
					preg_match("/^UPDATE( |\\t)+([^ \\t\\(]+)/i", $sql, $matches);
					$table = (isset($matches[2]) && $matches[2]) ? $matches[2] : null;
				}
				if(!$table) {
					preg_match("/FROM( |\\t)+([^ \\t\\(]+)( |\\t)+WHERE/i", $sql, $matches);
					$table = (isset($matches[2]) && $matches[2]) ? $matches[2] : null;
				}

				$e = new sqlException($e->getMessage(), sqlException::CODE_FIELD_NF, $e);
				$e->setData([$table, $field]);
				throw $e;
			}

			throw $e;
		}

		if(!$result) {
			$this->timer->stop();
			throw new Exception($query->errorCode().$query->errorInfo());
		}
		$this->timer->stop();
		return $query;
	}

	public function getLastInsertId() {
		return $this->connection->lastInsertId();
	}

	public function selectDb($dbname) {
		return $this->directQuery('USE `'.$dbname.'`');
	}

	protected function __construct($config_name, $without_select_db = false) {
		$config = config::get('mysql'.($config_name ? '-'.$config_name : ''));

		$attributes = [];
		if ( isset($config['persist']) && $config['persist'] ) $attributes[PDO::ATTR_PERSISTENT] = true;
		$attributes[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;

		$params = "mysql:host={$config['host']}";
		if(!$without_select_db)
			(isset($config['db']) && $config['db']) ? $params .= ";dbname={$config['db']}" : null;
		(isset($config['port']) && $config['port']) ? $params .= ";port={$config['port']}" : null;

		$this->timer = new timer();
		try {
			$this->connection = new PDO($params, $config['user'], $config['password'], $attributes);
		} catch (PDOException $e) {
			$this->timer->stop();
			if($e->getCode() == 1049) {
				$e = new sqlException($e->getMessage(), sqlException::CODE_DB_NF, $e);
				$e->setData($config['db']);
			}
			throw $e;
		}

		if((int)($e = $this->connection->errorCode())) {
			$this->timer->stop();
			throw new Exception(print_r($e, true));
		}

		if ( isset($config['charset']) && $config['charset'] ){
			$this->directQuery('SET NAMES ' . $config['charset']);
		}

		$this->timer->stop();
	}

	public function createDB($db_name) {
		return $this->directQuery('CREATE DATABASE `'.$db_name.'`');
	}

	public function createTable($table, $id_name) {
		$sql = '
		CREATE TABLE `'.$table.'` (
		  `'.$id_name.'` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  PRIMARY KEY (`'.$id_name.'`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		';

		return $this->directQuery($sql);
	}

	public function addField($table, $field_name) {

		if(preg_match('/^(.*)_id$/', $field_name) || preg_match('/^(.*)_num$/', $field_name)) {
			$type = 'INT(11) NULL DEFAULT NULL';

		} else if(preg_match('/^(.*)_dbl$/', $field_name)) {
			$type = 'DOUBLE NULL DEFAULT NULL';

		} else if(preg_match('/^(.*)_text$/', $field_name)) {
			$type = 'LONGTEXT NULL DEFAULT NULL';

		} else if(preg_match('/^is_(.*)$/', $field_name)) {
			$type = 'TINYINT(1) NULL DEFAULT NULL';

		} else if(preg_match('/^(.*)_dt$/', $field_name)) {
			$type = 'DATETIME NULL DEFAULT NULL';

		} else if(preg_match('/^(.*)_ts$/', $field_name)) {
			$type = 'TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP';

		} else {
			$type = 'TEXT NULL DEFAULT NULL';
		}

		$sql = 'ALTER TABLE `'.$table.'` ADD `'.$field_name.'` '.$type;
		return $this->directQuery($sql);
	}

	public function getNow() {
		return date('Y-m-d H:i:s');
	}
}
