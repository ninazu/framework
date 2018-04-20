<?php

namespace vendor\ninazu\framework\Component\Db;

use ErrorException;
use vendor\ninazu\framework\Component\Db\Interfaces\IBasicQuery;
use vendor\ninazu\framework\Component\Db\Interfaces\IQuery;
use vendor\ninazu\framework\Component\Db\Interfaces\IQueryPrepare;
use vendor\ninazu\framework\Component\Db\Interfaces\IQueryResult;

class Query implements IBasicQuery, IQuery, IQueryPrepare, IQueryResult {

	/**@var string $query */
	protected $query = '';

	/**@var array[] $debugLog */
	protected $bindsIntegers = [];

	/**@var array[] $debugLog */
	protected $binds = [];

	/**@var array[] $debugLog */
	protected $bindsArray = [];

	/**@var \PDOStatement $statement */
	protected $statement = null;

	/**@var Connection */
	protected $connection = null;

	public function __construct($connection) {
		$this->connection = $connection;
	}

	/**@internal
	 *
	 * @param string $query
	 *
	 * @return $this
	 */
	public function setQuery($query) {
		$this->query = $query;

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function binds(array $binds) {
		foreach ($binds as $placeholder => $value) {
			$this->bind($placeholder, $value);
		}

		if (count($this->binds) > Mysql::MAX_BINDS_COUNT) {
			$this->connection->error('Too many binds');
		}

		return $this;
	}

	public function bindIntegers(array $binds) {
		if (array_keys($binds) !== range(0, count($binds) - 1)) {
			throw new ErrorException('bindIntegers allowed only sequential array');
		}

		$this->bindsIntegers = $binds;

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function bindArray($placeholder, array $values) {
		if (isset($this->bindArrays[$placeholder]) || isset($this->binds[$placeholder])) {
			$this->connection->error("Placeholder collision '{$placeholder}'", $this->query, $this->binds);
		}

		$this->bindsArray[$placeholder] = $values;

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function execute() {
		$this->injectBindArray($sql, $binds, true);
		$this->statement = $this->connection->execute(static::class, $sql, $binds, $this->bindsIntegers);

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function getSQL(&$sql, $withPlaceholders = true) {
		$sql = $this->query;

		if (!$withPlaceholders) {
			foreach ($this->bindsIntegers as $index => $integer) {
				self::replaceIntegerPlaceholder($index, (int)$integer, $sql);
			}

			$this->injectBindArray($sql, $binds);

			foreach ($binds as $placeholder => $value) {
				$value = $this->connection->quote(stripslashes($value));

				self::replacePlaceholder($placeholder, $value, $sql);
			}
		}

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function columnCount() {
		return $this->statement->columnCount();
	}

	/**
	 * @inheritdoc
	 */
	public function rowCount() {
		return $this->statement->rowCount();
	}

	/**
	 * @inheritdoc
	 */
	public function columnMeta($columnIndex) {
		return $this->statement->getColumnMeta($columnIndex);
	}

	protected function injectBindArray(&$sql, &$binds, $executeScenario = true) {
		$binds = $this->binds;
		$sql = $this->query;
		$delimiter = $executeScenario ? '_' : '';

		foreach ($this->bindsArray as $placeholder => $values) {
			$newBinds = [];

			foreach ($values as $index => $value) {
				$autoPlaceholder = "{$placeholder}_{$index}";

				self::validatePlaceholderAndFormat($autoPlaceholder, $value, $delimiter);

				if (isset($binds[$autoPlaceholder])) {
					$this->connection->error("Placeholder collision '{$autoPlaceholder}'", $sql, $binds);
				}

				$binds[$autoPlaceholder] = $value;
				$newBinds[] = $autoPlaceholder;
			}

			self::replacePlaceholder($placeholder, implode(',', $newBinds), $sql);
		}

		if (count($binds) > Mysql::MAX_BINDS_COUNT) {
			$this->connection->error('Too many binds');
		}
	}

	protected function reset() {
		$this->binds = [];
		$this->bindsArray = [];
		$this->statement = null;
	}

	//TODO Incomplete
	protected static function replaceIntegerPlaceholder($index, $value, &$sql) {
		//$sql = preg_replace("/\\{$placeholder}([^a-z0-9_]+)|\\{$placeholder}$/i", "{$value}$1", $sql);
	}

	//TODO Incomplete
	protected static function replacePlaceholder($placeholder, $value, &$sql) {
		$sql = preg_replace("/\\{$placeholder}([^a-z0-9_]+)|\\{$placeholder}$/i", "{$value}$1", $sql);
	}

	/**
	 * @param string $placeholder
	 * @param string|int|float|\DateTime
	 *
	 * @return $this
	 *
	 * @throws ErrorException
	 */
	private function bind($placeholder, $value) {
		self::validatePlaceholderAndFormat($placeholder, $value);

		if (isset($this->binds[$placeholder])) {
			throw new ErrorException("Placeholder collision '{$placeholder}'");
		}

		$this->binds[$placeholder] = $value;

		return $this;
	}

	private static function validatePlaceholderAndFormat(&$placeholder, &$value, $autoDelimiter = '') {
		if (empty($placeholder)) {
			throw new ErrorException('Empty string passed as placeholder');
		}

		if ($placeholder[0] !== ':') {
			$placeholder = ':' . $placeholder;
		}

		if (preg_match("/[^:a-z0-9{$autoDelimiter}]/i", $placeholder)) {
			throw new ErrorException("Wrong placeholder name '{$placeholder}'");
		}

		if (!is_scalar($value)) {
			if (is_array($value)) {
				throw new ErrorException("You try bind array as placeholder '{$placeholder}', if you sure to bind a list of values, use method bindArray(\$placeholder, \$arrayOfValues)");
			} else if ($value instanceof \DateTime) {
				$value = $value->format(Mysql::FORMAT_DATETIME);
			} else {
				throw new ErrorException("Trying to bind not scalar nor DateTime as placeholder '{$placeholder}'");
			}
		}
	}
}