<?php

namespace vendor\ninazu\framework\Component\Db;

use RuntimeException;
use vendor\ninazu\framework\Component\Db\Interfaces\IUpdate;
use vendor\ninazu\framework\Component\Db\Interfaces\IUpdateResult;
use vendor\ninazu\framework\Component\Db\SQLParser\Lexer;
use vendor\ninazu\framework\Component\Db\SQLParser\Processor;
use vendor\ninazu\framework\Helper\Formatter;

class UpdateQuery extends WritableQuery implements IUpdate, IUpdateResult {

	use WritableValues;
	use WritableWhere;
	use OrderAndLimit;

	/**
	 * @inheritdoc
	 */
	public function where($string) {
		$this->setWhere($string);

		return $this;
	}

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
	 * @internal
	 *
	 * @inheritdoc
	 */
	public function priority($scenario) {
		if ($scenario != self::PRIORITY_LOW) {
			throw new RuntimeException('Wrong value of priority. Please use InsertQuery::PRIORITY_* constants');
		}

		$this->priority = $scenario;

		return $this;
	}

	/**
	 * @param array $values
	 *
	 * @return bool

	 */
	public function validateValues(array &$values) {
		foreach ($values as $key => $value) {
			if (is_numeric($key)) {
				throw new RuntimeException('Values must be a associative array');
			}

			self::checkColumnName($key);

			if (!is_scalar($value) && !$value instanceof Expression) {
				if (is_null($value)) {
					$values[$key] = Mysql::Expression('NULL');

					continue;
				}

				throw new RuntimeException('Value must be a scalar');
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
		$this->query = "UPDATE{$this->priority}{$this->onError}{$this->table}\nSET\n{$values}\n{$this->where}{$this->orderBy}{$this->limit}";
		$this->query = Formatter::removeLeftTabs($this->query);

		$result[] = [
			'bindsString' => $this->bindsString,
			'query' => $this->query,
		];

		return $result;
	}
}