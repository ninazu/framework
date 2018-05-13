<?php

namespace vendor\ninazu\framework\Component\Db;

use ErrorException;
use vendor\ninazu\framework\Component\Db\Interfaces\IInsert;
use vendor\ninazu\framework\Component\Db\Interfaces\IInsertResult;
use vendor\ninazu\framework\Component\Db\SQLParser\Lexer;
use vendor\ninazu\framework\Component\Db\SQLParser\Processor;

class InsertQuery extends WritableQuery implements IInsert, IInsertResult {

	use WritableValues;

	private $columnsUpdate = '';

	/**
	 * @inheritdoc
	 */
	public function getSQL(&$query, $withPlaceholders) {
		$parts = $this->prepareSql();

		$tmpQuery = $this->query;

		$tmpBindsString = $this->bindsString;
		$tmpBindsArray = $this->bindsArray;
		$tmpBindsInteger = $this->bindsInteger;

		$this->bindsArray = [];

		foreach ($parts as $part) {
			$this->query = $part['query'];
			$this->bindsString = $part['binds'];

			if (!$withPlaceholders) {
//				$placeholders = (new Processor(Lexer::parse($this->query)))->getPlaceholders();
//
//				$this->injectBindArray($query, $binds, $placeholders, true);

//				foreach ($binds as $placeholder => $value) {
//					$value = $this->connection->quote(stripslashes($value));
//
//					self::replacePlaceholder($placeholder, $value, $this->query);
//				}
			}

			$query .= "{$this->query};\n\n";
		}

		$this->query = $tmpQuery;
		$this->bindsString = $tmpBindsString;
		$this->bindsArray = $tmpBindsArray;
		$this->bindsInteger = $tmpBindsInteger;

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function execute() {
		$parts = $this->prepareSql();

		$transaction = $this->connection->beginTransaction();
		$this->bindsArray = [];

		try {
			foreach ($parts as $part) {
				$this->bindsString = $part['binds'];
				$this->query = $part['query'];
				parent::execute();

				$this->affectedRows += $this->statement->rowCount();
			}

			$transaction->commit();
		} catch (\Exception $exception) {
			$transaction->rollback();

			throw $exception;
		}

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function onDuplicate($scenario, $columnUpdate = []) {
		if (!in_array($scenario, [self::ON_DUPLICATE_UPDATE, self::ON_DUPLICATE_IGNORE])) {
			throw new ErrorException('Wrong value of onDuplicate. Please use InsertQuery::ON_DUPLICATE_* constants');
		}

		$this->onError = $scenario;

		if ($scenario === self::ON_DUPLICATE_UPDATE) {
			if (empty($columnUpdate)) {
				throw new ErrorException('Empty values for update');
			}

			$valuesForUpdate = [];
			$binds = [];

			foreach ($columnUpdate as $columnsUpdateName => $columnsUpdateValue) {
				$columnsUpdateValue = trim($columnsUpdateValue);

				if ($columnsUpdateValue instanceof Expression) {
					$valuesForUpdate[] = "`{$columnsUpdateName}` = {$columnsUpdateValue}\n";
				} else {
					$autoPlaceholder = ":{$columnsUpdateName}_forUpdate";
					$valuesForUpdate[] = "`{$columnsUpdateName}` = {$autoPlaceholder}\n";
					$binds[$autoPlaceholder] = $columnsUpdateValue;
				}
			}

			$this->bindsString($binds);

			$this->columnsUpdate = implode(", ", $valuesForUpdate);
		}

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function lastInsertedId() {
		return $this->connection->lastInsertedId();
	}

	public function reset() {
		parent::reset();
		$this->affectedRows = 0;
		$this->priority = null;
		$this->values = null;
		$this->table = null;
		$this->onError = null;
		$this->columnsUpdate = '';
	}

	/**
	 * @inheritdoc
	 */
	public function priority($scenario) {
		if (!in_array($scenario, [self::PRIORITY_DELAYED, self::PRIORITY_HIGH, self::PRIORITY_LOW])) {
			throw new ErrorException('Wrong value of priority. Please use InsertQuery::PRIORITY_* constants');
		}

		$this->priority = $scenario;

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function validateValues(array $values) {
		$firstRow = reset($values);

		if (!is_array($firstRow)) {
			throw new ErrorException('Values is not arrays of array');
		}

		$columnNames = array_keys($firstRow);

		foreach ($columnNames as $column) {
			if (preg_match('/[^a-z0-9_-]/i', $column)) {
				throw new ErrorException("Column name '{$column}' contains invalid characters.");
			}
		}

		foreach ($values as $index => $row) {
			if (array_diff($columnNames, array_keys($row))) {
				throw new ErrorException("Keys of Values are different for index #{$index}");
			}

			foreach ($columnNames as $column) {
				if (!isset($row[$column])) {
					throw new ErrorException("One of the rows does not contain value for the column '{$column}'.");
				}
			}
		}

		return true;
	}

	/**
	 * Split a query
	 *
	 * @return array
	 * @throws MySQLException
	 */
	private function prepareSql() {
		$ignore = ($this->onError == self::ON_DUPLICATE_IGNORE) ? self::ON_DUPLICATE_IGNORE : '';
		$update = '';
		$priority = ($this->priority) ? $this->priority : '';
		$binds = $this->bindsString;

		if ($this->onError == self::ON_DUPLICATE_UPDATE) {
			$update = "\n" . self::ON_DUPLICATE_UPDATE . "\n";
			$update .= $this->columnsUpdate;
		}

		$columns = array_keys(reset($this->values));
		$columnNames = implode("`,\n\t`", $columns);
		$headerQuery = "INSERT{$priority}{$ignore}\nINTO {$this->table}\n(\n\t`{$columnNames}`\n)\nVALUES\n\t";
		$allowedLength = ($this->connection->getMaxQueryLength() - strlen($headerQuery)) * 0.95;
		$parts = [];
		$currentPart = [];
		$partLength = 0;

		//Calculate row len
		foreach ($this->values as $row) {
			$values = implode("', '", $row);
			$rowLength = strlen("('{$values}'),\n");

			if ($rowLength > $allowedLength) {
				$roundedRowLength = round($rowLength / 1024, 0);
				$roundedAllowedLength = round($allowedLength / 1024, 0);
				$error = "Too much data to insert in one row. Attempted to insert {$roundedRowLength} Kb in one row when a maximum of {$roundedAllowedLength} Kb is allowed.";
				$this->connection->error($error, $headerQuery, $row);
			}

			$partLength += $rowLength;

			if ($partLength < $allowedLength) {
				$currentPart[] = $row;
			} else {
				$parts[] = $currentPart;
				$currentPart = [$row];
				$partLength = $rowLength;
			}
		}

		$parts[] = $currentPart;
		$result = [];

		foreach ($parts as $index => $part) {
			list($partialQuery, $partialBinds) = $this->partialSQL($index, $binds, $headerQuery, $part, $columns, $update);
			$result[$index] = [
				'binds' => array_replace_recursive($binds, $partialBinds),
				'query' => $partialQuery,
			];
		}

		return $result;
	}

	private function partialSQL($index, $binds, $headerQuery, $values, $columns, $update) {
		$lines = [];

		foreach ($values as $i => $row) {
			$placeholders = [];

			foreach ($columns as $column) {
				$value = $row[$column];

				if ($value instanceof Expression) {
					$placeholders[] = (string)$value;
				} else {
					$name = ":{$column}_{$index}_{$i}";
					$binds[$name] = $value;
					$placeholders[] = $name;
				}
			}

			$placeholders = implode(', ', $placeholders);
			$lines[] = "({$placeholders}),";
		}

		$query = $headerQuery . implode("\n\t", $lines);
		$query = rtrim($query, ',') . $update;

		return [
			$query,
			$binds,
		];
	}
}