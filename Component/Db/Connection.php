<?php

namespace vendor\ninazu\framework\Component\Db;

use DateTime;
use ErrorException;
use PDO;
use PDOException;
use vendor\ninazu\framework\Component\Db\Interfaces\IConnection;
use vendor\ninazu\framework\Component\Db\Interfaces\ITransaction;
use vendor\ninazu\framework\Core\BaseConfigurator;
use vendor\ninazu\framework\Helper\Reflector;

class Connection extends BaseConfigurator implements IConnection, ITransaction {

	#region  Configurator

	protected $hostname;

	protected $username;

	protected $password;

	protected $schema;

	protected $port = 3306;

	protected $charset = 'utf8_unicode_ci';

	protected $maxQueryLength = 5 * 1024 * 1024;

	protected $PDOOptions = [
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::ATTR_PERSISTENT => false,
	];

	#endregion

	/**@var Mysql $connectionStack */
	private $connectionStack;

	/**@var string $name */
	private $name;

	/**@var PDO $adapter */
	private $adapter;

	/**@var int $transactionCounter */
	private $transactionCounter = 0;

	private $rollBackMessage;

	/**@var bool $debugEnabled */
	private $debugEnabled = false;

	/**@var array[] $debugLog */
	private $debugLog = [];

	#region Interface

	/**
	 * @internal
	 *
	 * @param string $name
	 * @param Mysql $parentClass
	 */
	public function __construct($name, $parentClass) {
		$this->connectionStack = $parentClass;
		$this->name = $name;
	}

	/**
	 * @inheritdoc
	 */
	public function beginTransaction() {
		if ($this->transactionCounter == 0) {
			if (!$this->adapter->beginTransaction()) {
				$this->error('Failed to start a transaction');
			}
		}

		$this->transactionCounter++;

		if ($this->debugEnabled) {
			$this->debugLog['transactions']['begin_' . $this->transactionCounter] = array_slice(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), 0, 4);
		}

		return $this;
	}

	/**
	 * @internal
	 *
	 * @param string $message
	 * @param string $query
	 * @param array|null $binds
	 *
	 * @throws MySQLException
	 */
	public function error($message, $query = '', array $binds = null) {
		$exception = new MySQLException($this->hostname, $this->name, $message, $query, $binds, $this->PDOOptions);

		throw $exception;
	}

	/**
	 * @inheritdoc
	 */
	public function commit() {
		if ($this->transactionCounter == 0) {
			$this->error('Commit called without active transaction.');
		}

		if ($this->debugEnabled) {
			$this->debugLog['transactions']['commit_' . $this->transactionCounter] = array_slice(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), 0, 4);
		}

		$this->transactionCounter--;

		if ($this->transactionCounter == 0) {
			if (!$this->adapter->commit()) {
				$this->error('Failed to commit a transaction');
			}
		}

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function rollback($rollBackMessage = null) {
		if ($this->debugEnabled) {
			$this->debugLog['transactions']['rollback'] = array_slice(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), 0, 4);
		}

		if ($this->adapter && $this->transactionCounter && !$this->adapter->rollBack()) {
			$this->error('Failed to rollback a transaction');
		}

		if (!is_null($rollBackMessage)) {
			$this->rollBackMessage = $rollBackMessage;
		}

		$this->transactionCounter = 0;

		return $this;
	}

	public function getRollBackMessage() {
		return $this->rollBackMessage;
	}

	/**
	 * @inheritdoc
	 */
	public function query($query) {
		return (new Query($this))
			->setQuery($query);
	}

	/**
	 * @inheritdoc
	 */
	public function select($query) {
		return (new SelectQuery($this))
			->setQuery($query);
	}

	/**
	 * @inheritdoc
	 */
	public function insert($table, $values, $columns = []) {
		return (new InsertQuery($this))
			->setTable($table)
			->setValues($values, is_array($values))
			->setColumns($columns);
	}

	/**
	 * @inheritdoc
	 */
	public function update($table, array $values) {
		return (new UpdateQuery($this))
			->setTable($table)
			->setValues($values, is_array($values));
	}

	/**
	 * @inheritdoc
	 */
	public function delete($table, $where) {
		return (new DeleteQuery($this))
			->setTable($table)
			->setWhere($where);
	}

	/**
	 * @inheritdoc
	 */
	public function replace($table) {
		// TODO: Implement replace() method.
	}

	#endregion

	/**
	 * @inheritdoc
	 */
	public function debugEnable() {
		$this->debugEnabled = true;

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function debugDisable() {
		$this->debugEnabled = false;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getMaxQueryLength() {
		return $this->maxQueryLength;
	}

	/**
	 * @internal
	 * @param string $instance
	 * @param string $query
	 * @param array $binds
	 * @param array $bindsInteger
	 *
	 * @return bool|null|\PDOStatement
	 *
	 * @throws MySQLException
	 */
	public function execute($instance, $query, array $binds, array $bindsInteger) {
		try {
			/**@var $startTime */
			if ($this->debugEnabled) {
				$startTime = microtime(true);
			}

			// execute query
			if (empty($binds) && empty($bindsInteger)) {
				$statement = $this->adapter->query($query);
			} else {
				$statement = $this->adapter->prepare($query);

				foreach ($bindsInteger as $key => $value) {
					$statement->bindValue($key, $value, PDO::PARAM_INT);
				}

				foreach ($binds as $key => $value) {
					$statement->bindValue($key, $value, PDO::PARAM_STR);
				}

				$statement->execute();
			}

			if ($this->debugEnabled) {
				preg_match('/\\\\(\w+)QUERY$/i', $instance, $match);
				$instance = strtoupper($match[1]);

				if (!empty($binds) && in_array($instance, ['INSERT', 'REPLACE'])) {
					$binds = array_slice($binds, 0, 20);
					$binds = array_slice($binds, 0, 20);
				}

				$this->debugLog['queries'][] = [
					'executionTime' => round(microtime(true) - $startTime, 3),
					'connection' => $this->adapter->getAttribute(PDO::ATTR_CONNECTION_STATUS),
					'query' => $statement->queryString,
					'binds' => $binds,
				];
			}

			return $statement;
		} catch (PDOException $exception) {
			$this->disconnect();
			$this->error($exception->getMessage(), $query, $binds);

			return null;
		}
	}

	/**
	 * @internal
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	public function quote($value) {
		return $this->adapter->quote($value);
	}

	/**
	 * @return string
	 */
	public function lastInsertedId() {
		return $this->adapter->lastInsertId();
	}

	/**
	 * @internal
	 *
	 * @return $this
	 *
	 * @throws ErrorException
	 * @throws MySQLException
	 */
	public function connect() {
		foreach (['hostname', 'username', 'schema'] as $property) {
			if (empty($this->$property)) {
				throw new ErrorException("Required connection parameter '{$property}' is not set or empty for database configuration: {$this->name}");
			}
		}

		if ($this->maxQueryLength < 1024) {
			throw new ErrorException("Parameter 'maxQueryLength' is less than 1kb or wrong for database connection: {$this->name}");
		}

		//Overwrite params
		$this->PDOOptions[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;

		$dataSourceName = "mysql:host={$this->hostname};port={$this->port};dbname={$this->schema};charset={$this->charset};";

		try {
			$this->adapter = new PDO($dataSourceName, $this->username, $this->password, $this->PDOOptions);
		} catch (PDOException $exception) {
			$this->error($exception->getMessage());
		}

		return $this;
	}

	public function setAttribute($attribute, $value) {
		$this->adapter->setAttribute($attribute, $value);

		//TODO Return to original
		//$this->adapter->getAttribute($attribute, $value);

		return $this;
	}

	/**
	 * @internal
	 */
	public function disconnect() {
		if ($this->adapter->inTransaction()) {
			$this->adapter->rollBack();
		}

		$this->adapter = null;
	}
}