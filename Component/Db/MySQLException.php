<?php

namespace vendor\ninazu\framework\Component\Db;

use Exception;
use PDO;

class MySQLException extends Exception {

	const INSERT_REPLACE_QUERY_MAX_LENGTH = 2048;

	const INSERT_REPLACE_BINDS_MAX_COUNT = 5;

	private $query;

	private $binds;

	private $host;

	private $name;

	private $PDOOptions;

	private $PDOOptionsReference = [
		PDO::ATTR_AUTOCOMMIT => 'PDO::ATTR_AUTOCOMMIT',
		PDO::ATTR_CASE => 'PDO::ATTR_CASE',
		PDO::ATTR_ERRMODE => 'PDO::ATTR_ERRMODE',
		PDO::ATTR_ORACLE_NULLS => 'PDO::ATTR_ORACLE_NULLS',
		PDO::ATTR_PERSISTENT => 'PDO::ATTR_PERSISTENT',
		PDO::ATTR_PREFETCH => 'PDO::ATTR_PREFETCH',
		PDO::ATTR_TIMEOUT => 'PDO::ATTR_TIMEOUT',
		PDO::ATTR_DEFAULT_FETCH_MODE => 'PDO::ATTR_DEFAULT_FETCH_MODE',
	];

	private $PDOOptionsValuesReference = [
		PDO::ATTR_ERRMODE => [
			PDO::ERRMODE_SILENT => 'PDO::ERRMODE_SILENT',
			PDO::ERRMODE_WARNING => 'PDO::ERRMODE_WARNING',
			PDO::ERRMODE_EXCEPTION => 'PDO::ERRMODE_EXCEPTION',
		],
		PDO::ATTR_DEFAULT_FETCH_MODE => [
			PDO::FETCH_ASSOC => 'PDO::FETCH_ASSOC',
			PDO::FETCH_BOTH => 'PDO::FETCH_BOTH',
			PDO::FETCH_BOUND => 'PDO::FETCH_BOUND',
			PDO::FETCH_CLASS => 'PDO::FETCH_CLASS',
			PDO::FETCH_INTO => 'PDO::FETCH_INTO',
			PDO::FETCH_LAZY => 'PDO::FETCH_LAZY',
			PDO::FETCH_NAMED => 'PDO::FETCH_NAMED',
			PDO::FETCH_NUM => 'PDO::FETCH_NUM',
			PDO::FETCH_OBJ => 'PDO::FETCH_OBJ',
		],
		PDO::ATTR_CASE => [
			PDO::CASE_NATURAL => 'PDO::CASE_NATURAL',
			PDO::CASE_LOWER => 'PDO::CASE_LOWER',
			PDO::CASE_UPPER => 'PDO::CASE_UPPER',
		],
		PDO::ATTR_AUTOCOMMIT => [
			true => 'true',
			false => 'false',
		],
		PDO::ATTR_ORACLE_NULLS => [
			true => 'true',
			false => 'false',
		],
		PDO::ATTR_PERSISTENT => [
			true => 'true',
			false => 'false',
		],
	];

	/**
	 * MySQLException constructor.
	 * @param string $host
	 * @param int $name
	 * @param string $message
	 * @param string $query
	 * @param array|null $binds
	 * @param array $PDOOptions
	 */
	public function __construct($host, $name, $message, $query = '', array $binds = null, array $PDOOptions = []) {
		$message = "Host: '{$host}'; Connection name: '{$name}'; Error: {$message}";

		parent::__construct($message, 0, null);

		$this->host = $host;
		$this->name = $name;

		if ((stripos(ltrim($query), 'insert') === 0) || (stripos(ltrim($query), 'replace') === 0)) {
			$queryLength = strlen($query);

			if ($queryLength > self::INSERT_REPLACE_QUERY_MAX_LENGTH) {
				$query = substr($query, 0, self::INSERT_REPLACE_QUERY_MAX_LENGTH) . "... (query length {$queryLength} bytes)";
			}

			if (count($binds) > self::INSERT_REPLACE_BINDS_MAX_COUNT) {
				$binds = array_slice($binds, 0, self::INSERT_REPLACE_BINDS_MAX_COUNT);
			}
		}

		$this->query = $query;
		$this->binds = $binds;
		$this->PDOOptions = $PDOOptions;
	}

	public function getDatabaseHost() {
		return $this->host;
	}

	public function getName() {
		return $this->name;
	}

	public function getQuery() {
		$length = strlen($this->query);

		if ($length > 2048) {
			return substr($this->query, 0, 2048) . "... (query length: {$length} bytes)";
		}

		return $this->query;
	}

	public function getBinds() {
		return $this->binds;
	}

	public function getPDOOptions() {
		$result = [];

		foreach ($this->PDOOptions as $option => $value) {
			if (isset($this->PDOOptionsReference[$option])) {
				if (isset($this->PDOOptionsValuesReference[$option][$value])) {
					$result[$this->PDOOptionsReference[$option]] = $this->PDOOptionsValuesReference[$option][$value];
				} else {
					$result[$this->PDOOptionsReference[$option]] = (string)$value;
				}
			} else {
				$result[(string)$option] = (string)$value;
			}
		}

		return $result;
	}
}