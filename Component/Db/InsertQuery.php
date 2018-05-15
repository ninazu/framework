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

	private $columns;

	/**
	 * @internal
	 *
	 * @param string[] $columns
	 *
	 * @return InsertQuery
	 *
	 * @throws ErrorException
	 */
	public function setColumns(array $columns) {
		$unique = [];

		foreach ($columns as $index => $column) {
			if (!is_string($column)) {
				throw new ErrorException("Column {$index} must be string");
			}

			self::checkColumnName($column);

			$unique[$column] = null;
		}

		$this->columns = array_keys($unique);

		return $this;
	}

//	/**
//	 * @inheritdoc
//	 */
//	public function getSQL(&$query, $withPlaceholders) {
//		$parts = $this->prepareSql();
//		$query = '';
//
//		foreach ($parts as $part) {
//			if (!$withPlaceholders) {
//				$placeholders = (new Processor(Lexer::parse($part['query'])))->getPlaceholders();
//
//				foreach ($this->bindsArray as $placeholder => $values) {
//					$this->replaceArrayPlaceholder($placeholder, $values, $part['query'], $placeholders, $part['bindsString'], false);
//				}
//
//				foreach ($this->bindsInteger as $placeholder => $value) {
//					self::replacePlaceholder($placeholder, $value, $part['query'], $placeholders);
//				}
//
//				foreach ($part['bindsString'] as $placeholder => $value) {
//					$value = $this->connection->quote(stripslashes($value));
//					self::replacePlaceholder($placeholder, $value, $part['query'], $placeholders);
//				}
//			}
//
//			$query .= "{$part['query']};\n\n";
//		}
//
//		$query = trim($query, "\n");
//
//		return $this;
//	}

	/**
	 * @inheritdoc
	 */
	public function execute() {
		$parts = $this->prepareSql();

		$transaction = $this->connection->beginTransaction();

		try {
			foreach ($parts as $part) {
				$this->bindsString = $part['bindsString'];
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

			if (!empty($this->columnsUpdate)) {
				throw new ErrorException('Column for update already set');
			}

			$placeholders = [];
			$bindsString = [];

			foreach ($columnUpdate as $column => $value) {
				self::checkColumnName($column);

				if ($value instanceof Expression) {
					//Use as is
					$value = "`{$column}` = {$value}";
				} else {
					//Append AutoPlaceholders
					$autoPlaceholder = ":{$column}_forUpdate";
					$bindsString[$autoPlaceholder] = $value;
					$value = "`{$column}` = {$autoPlaceholder}";
				}

				$placeholders[] = $value;
			}

			$this->bindsStringAndValidate($bindsString, true);
			$this->columnsUpdate = "\t" . implode(",\n\t", $placeholders);
		}

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function lastInsertedId() {
		return $this->connection->lastInsertedId();
	}

	protected function reset() {
		parent::reset();
		$this->affectedRows = 0;
		$this->priority = null;
		$this->values = null;
		$this->table = null;
		$this->onError = null;
		$this->columnsUpdate = '';
		$this->columns = [];
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
			self::checkColumnName($column);
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
	protected function prepareSql() {
		$onErrorIgnore = ($this->onError == self::ON_DUPLICATE_IGNORE) ? self::ON_DUPLICATE_IGNORE : '';
		$onDuplicateUpdate = '';
		$priority = ($this->priority) ? $this->priority : '';
		$bindsString = $this->bindsString;
		$headerQuery = "INSERT{$priority}{$onErrorIgnore}\nINTO {$this->table}\n";

		if ($this->onError == self::ON_DUPLICATE_UPDATE) {
			$onDuplicateUpdate = "\n" . self::ON_DUPLICATE_UPDATE . "\n";
			$onDuplicateUpdate .= $this->columnsUpdate;
		}

		if ($this->values instanceof Expression) {
			$columnNames = "";
			$expression = "\n{$this->values}\n";

			if (!empty($this->columns)) {
				$columnNames = implode("`,\n\t`", $this->columns);
				$columnNames = "(\n\t`{$columnNames}`\n)\n";
			}

			$partialQuery = "{$headerQuery}{$columnNames}({$expression}){$onDuplicateUpdate}";

			return [
				[
					'bindsString' => $bindsString,
					'query' => $partialQuery,
				],
			];
		}

		$columns = array_keys(reset($this->values));
		$columnNames = implode("`,\n\t`", $columns);
		$headerQuery .= "(\n\t`{$columnNames}`\n)\nVALUES\n\t";
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
			list($partialQuery, $partialBinds) = $this->partialSQL($index, $bindsString, $headerQuery, $part, $columns, $onDuplicateUpdate);
			$result[$index] = [
				'bindsString' => array_replace_recursive($bindsString, $partialBinds),
				'query' => $partialQuery,
			];
		}

		return $result;
	}

	private function partialSQL($index, $bindsString, $headerQuery, $values, $columns, $onDuplicateUpdate) {
		$lines = [];

		foreach ($values as $i => $row) {
			$placeholders = [];

			foreach ($columns as $column) {
				$value = $row[$column];

				if ($value instanceof Expression) {
					//Use as is
					$value = (string)$value;
				} else {
					//Append AutoPlaceholders
					$autoPlaceholder = ":{$column}_{$index}_{$i}";
					$bindsString[$autoPlaceholder] = $value;
					$value = $autoPlaceholder;
				}

				$placeholders[] = $value;
			}

			$placeholders = implode(', ', $placeholders);
			$lines[] = "({$placeholders}),";
		}

		$query = $headerQuery . implode("\n\t", $lines);
		$query = rtrim($query, ',') . "\n{$onDuplicateUpdate}";

		return [
			$query,
			$bindsString,
		];
	}
}