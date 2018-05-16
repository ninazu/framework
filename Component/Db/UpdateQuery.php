<?php

namespace vendor\ninazu\framework\Component\Db;

use ErrorException;
use vendor\ninazu\framework\Component\Db\Interfaces\IUpdate;
use vendor\ninazu\framework\Component\Db\Interfaces\IUpdateResult;
use vendor\ninazu\framework\Component\Db\SQLParser\Lexer;
use vendor\ninazu\framework\Component\Db\SQLParser\Processor;
use vendor\ninazu\framework\Helper\Formatter;

class UpdateQuery extends WritableQuery implements IUpdate, IUpdateResult {

	use WritableValues;
	use WritableWhere;

	private $limit;

	private $orderBy;

	/**
	 * @inheritdoc
	 */
	public function lowPriority() {
		return $this->priority(self::PRIORITY_LOW);
	}

	/**
	 * @inheritdoc
	 */
	public function ignoreErrors() {
		$this->onError = self::ON_ERROR_IGNORE;

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function orderBy(array $sequence) {
		foreach ($sequence as $expression) {
			if (!$expression instanceof Expression) {
				throw new ErrorException('Order by must be array of expressions');
			}
		}

		$this->orderBy = "ORDER BY " . implode(', ', $sequence);

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function limit($count) {
		if (!is_int($count) && !is_numeric($count)) {
			throw new ErrorException('Wrong value for limit');
		}

		$this->limit = "LIMIT {$count}";

		return $this;
	}

	/**
	 * @internal
	 *
	 * @inheritdoc
	 */
	public function priority($scenario) {
		if ($scenario != self::PRIORITY_LOW) {
			throw new ErrorException('Wrong value of priority. Please use InsertQuery::PRIORITY_* constants');
		}

		$this->priority = $scenario;

		return $this;
	}

	/**
	 * @param array $values
	 *
	 * @return bool
	 * @throws ErrorException
	 */
	public function validateValues(array $values) {
		foreach ($values as $key => $value) {
			if (is_numeric($key)) {
				throw new ErrorException('Values must be a associative array');
			}

			self::checkColumnName($key);

			if (!is_scalar($value) && !$value instanceof Expression) {
				throw new ErrorException('Value must be a scalar');
			}
		}

		return true;
	}

	protected function prepareSql() {
		$lines = [];

		foreach ($this->values as $key => $value) {
			if (!$value instanceof Expression) {
				//Append AutoPlaceholders
				$autoPlaceholder = ":{$key}_auto";
				$this->bindsString[$autoPlaceholder] = $value;
				$value = $autoPlaceholder;
			}

			$lines[] = "`{$key}` = {$value}";
		}

		$values = Formatter::addLeftTabs(implode(",\n", $lines), 1);
		$query = "UPDATE{$this->priority}{$this->onError}{$this->table}\nSET\n{$values}\n{$this->where}\n{$this->orderBy}\n{$this->limit}";
		$this->query = Formatter::removeLeftTabs($query);

		$result[] = [
			'bindsString' => $this->bindsString,
			'query' => $this->query,
		];

		return $result;
	}
}