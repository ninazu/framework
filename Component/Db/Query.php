<?php

namespace vendor\ninazu\framework\Component\Db;

use ErrorException;
use vendor\ninazu\framework\Component\Db\Interfaces\IBasicQuery;
use vendor\ninazu\framework\Component\Db\Interfaces\IQuery;
use vendor\ninazu\framework\Component\Db\Interfaces\IQueryPrepare;
use vendor\ninazu\framework\Component\Db\Interfaces\IQueryResult;
use vendor\ninazu\framework\Component\Db\SQLParser\Lexer;
use vendor\ninazu\framework\Component\Db\SQLParser\Processor;

class Query implements IBasicQuery, IQuery, IQueryPrepare, IQueryResult {

	/**@var string $query */
	protected $query = '';

	/**@var array[] $debugLog */
	protected $bindsInteger = [];

	/**@var string[] $bindsString */
	protected $bindsString = [];

	/**@var array[] $bindsArray */
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

	/**
	 * @inheritdoc
	 */
	public function bindsString(array $binds) {
		if (empty($binds)) {
			return $this;
		}

		foreach ($binds as $placeholder => $value) {
			$this->checkPlaceholder($placeholder, $value, false);
			$this->bindsString[$placeholder] = $value;
		}

		$this->checkBindsCount();

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function bindsInteger(array $binds) {
		if (empty($binds)) {
			return $this;
		}

		foreach ($binds as $placeholder => $value) {
			$this->checkPlaceholder($placeholder, $value, false);
			$this->bindsInteger[$placeholder] = (int)$value;
		}

		$this->checkBindsCount();

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function bindsArray($placeholder, array $binds) {
		if (empty($binds)) {
			throw new ErrorException('bindArray empty array given');
		}

		$dummy = '';
		//TODO Dummy
		$this->checkPlaceholder($placeholder, $dummy, false);
		$this->bindsArray[$placeholder] = $binds;

		$this->checkBindsCount();

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function execute() {
		$placeholders = (new Processor(Lexer::parse($this->query)))->getPlaceholders();
		//ByRef
		$query = $this->query;
		$bindsString = $this->bindsString;

		foreach ($this->bindsArray as $placeholder => $values) {
			$this->replaceArrayPlaceholder($placeholder, $values, $query, $placeholders, $bindsString, true);
		}

		$this->statement = $this->connection->execute(static::class, $query, $bindsString, $this->bindsInteger);

		return $this;
	}

	/**
	 * @internal
	 * @inheritdoc
	 */
	public function getSQL(&$query, $withPlaceholders) {
		$query = $this->query;

		if (!$withPlaceholders) {
			$placeholders = (new Processor(Lexer::parse($query)))->getPlaceholders();
			//ByRef
			$bindsString = $this->bindsString;

			foreach ($this->bindsArray as $placeholder => $values) {
				$this->replaceArrayPlaceholder($placeholder, $values, $query, $placeholders, $bindsString, false);
			}

			foreach ($this->bindsInteger as $placeholder => $value) {
				self::replacePlaceholder($placeholder, $value, $query, $placeholders);
			}

			foreach ($this->bindsString as $placeholder => $value) {
				$value = $this->connection->quote(stripslashes($value));
				self::replacePlaceholder($placeholder, $value, $query, $placeholders);
			}

			$this->checkBindsCount();
		}

		return $this;
	}

	protected function replaceArrayPlaceholder($placeholder, $values, &$query, array &$placeholders, &$bindsString, $executeScenario) {
		$newBinds = [];

		foreach ($values as $index => $value) {
			$autoPlaceholder = "{$placeholder}_{$index}";
			$this->checkPlaceholder($autoPlaceholder, $value, true);

			if ($executeScenario) {
				//Append AutoPlaceholder
				$bindsString[$autoPlaceholder] = $value;
			} else {
				//Replace Placeholder with QuotedValue
				$autoPlaceholder = $this->connection->quote(stripslashes($value));
			}

			$newBinds[] = $autoPlaceholder;
		}

		$value = implode(',', $newBinds);
		self::replacePlaceholder($placeholder, $value, $query, $placeholders);
	}

	protected function reset() {
		$this->bindsString = [];
		$this->bindsArray = [];
		$this->bindsInteger = [];
		$this->statement = null;
	}

	/**
	 * @deprecated
	 *
	 * @param int $index
	 * @param int $value
	 * @param string $sql
	 * @param array $placeholders
	 */
	protected static function replaceIntegerPlaceholder($index, $value, &$sql, array &$placeholders) {
		$value = (string)$value;
		$strValueLen = strlen((string)$value);
		$placeholderLen = 1;
		$currentPosition = $placeholders['?'][$index];
		$offset = ($strValueLen - $placeholderLen);

		unset($placeholders['?'][$index]);

		foreach ($placeholders as $name => $null) {
			foreach ($placeholders[$name] as $key => $position) {
				if ($position['pos'] < $currentPosition['pos']) {
					continue;
				}

				$placeholders[$name][$key]['pos'] = $position['pos'] + $offset;
			}
		}

		$sql = substr_replace($sql, $value, $currentPosition['pos'], $placeholderLen);
	}

	protected static function replacePlaceholder($placeholder, $value, &$sql, array &$placeholders) {
		$strValueLen = strlen($value);
		$placeholderLen = strlen($placeholder);
		$currentPositions = $placeholders[$placeholder];
		$offset = ($strValueLen - $placeholderLen);
		$tmpOffset = 0;

		foreach ($currentPositions as $index => $currentPosition) {
			unset($placeholders[$placeholder][$index]);

			foreach ($placeholders as $name => $null) {
				foreach ($placeholders[$name] as $key => $position) {
					if ($position['pos'] < $currentPosition['pos']) {
						continue;
					}

					$placeholders[$name][$key]['pos'] = $position['pos'] + $offset;
				}
			}

			$sql = substr_replace($sql, $value, $currentPosition['pos'] + $tmpOffset, $placeholderLen);
			$tmpOffset += $offset;
		}
	}

	protected function checkPlaceholder(&$placeholder, &$value, $autoPlaceholder) {
		if (isset($this->bindArrays[$placeholder]) || isset($this->bindsString[$placeholder]) || isset($this->bindsInteger[$placeholder])) {
			$this->connection->error("Placeholder collision '{$placeholder}'", $this->query, $this->bindsString);
		}

		if (empty($placeholder)) {
			throw new ErrorException('Empty string passed as placeholder');
		}

		if ($placeholder[0] !== ':') {
			$placeholder = ':' . $placeholder;
		}

		$autoDelimiter = $autoPlaceholder ? '_' : '';

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

	protected function checkBindsCount() {
		$flatCount = count($this->bindsString) + count($this->bindsInteger);
		$arrayCount = 0;

		foreach ($this->bindsArray as $values) {
			$arrayCount += count($values);
		}

		if (($flatCount + $arrayCount) > Mysql::MAX_BINDS_COUNT) {
			$this->connection->error('Too many binds');
		}
	}
}