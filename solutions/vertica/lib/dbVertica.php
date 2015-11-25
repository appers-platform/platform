<?
namespace solutions\vertica;

use dbSql;
use Exception;
use PDO;
use timer;

class dbVertica extends dbSql {
	/**
	 * @var timer timer
	 */
	protected $timer;
	/**
	 * @var PDO $connection
	 */
	protected $connection;

	protected $splitter = '"';

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
		} catch (Exception $e) {
			throw new Exception(
				$e->getMessage().' SQL: '.$sql,
				$e->getCode(),
				$e
			);
		}
		$this->timer->stop();

		if (stripos(trim($sql), 'SELECT') !== 0 && stripos(trim($sql), 'COMMIT') !== 0) {
            $this->directQuery('COMMIT;');
        }
		
		return $query;
	}

	public function getLastInsertId() {
		$this->getScalar('SELECT LAST_INSERT_ID()');
	}

	public function selectDb($dbname) {
		throw new Exception('Invalid method');
	}

	public function createTable($table, $id_name) {
		throw new Exception('Invalid method');
	}

	public function addField($table, $field_name) {
		throw new Exception('Invalid method');
	}

	protected function __construct($config_name, $without_select_db = false) {
		$config = \solutions\vertica::getConfig('connection'.($config_name ? '-'.$config_name : ''));

		if($without_select_db || !isset($config['db']) || !$config['db']) {
			throw new Exception('Can\\\'t connect to Vertica without DB');
		}

		$attributes = [];
		if ( isset($config['persist']) && $config['persist'] ) $attributes[PDO::ATTR_PERSISTENT] = true;
		$attributes[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
		$attributes[PDO::ATTR_EMULATE_PREPARES] = true;
		#$attributes[PDO::ATTR_TIMEOUT] = -1;

		$params = "pgsql:host={$config['host']}";
		(isset($config['db']) && $config['db']) ? $params .= ";dbname={$config['db']}" : null;
		(isset($config['port']) && $config['port']) ? $params .= ";port={$config['port']}" : null;

		$this->timer = new timer();
		$this->connection = new PDO($params, $config['user'], $config['password'], $attributes);
		$this->timer->stop();
	}

	public function createDB($db_name) {
		return $this->query('CREATE SCHEMA `'.$db_name.'`');
	}

	public function getNow() {
		return date('Y-m-d H:i:s');
	}
}
