<?php

namespace vendor\ninazu\framework\Component\Db;

use ErrorException;
use vendor\ninazu\framework\Component\Db\Interfaces\IUpdate;
use vendor\ninazu\framework\Component\Db\Interfaces\IUpdateResult;

class UpdateQuery extends WritableQuery implements IUpdate, IUpdateResult {

	use WritableValues;
	use WritableWhere;

	const ON_ERROR_IGNORE = ' IGNORE';

	const PRIORITY_LOW = ' LOW_PRIORITY';

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
	public function ignoreError() {
		$this->onError = self::ON_ERROR_IGNORE;

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function orderBy(array $sequence) {
//		foreach ($sequence){
//
//		}

		$this->orderBy = $sequence;

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function limit($count) {
		if (!is_int($count)) {
			throw new ErrorException('Wrong value for limit');
		}

		$this->limit = $count;

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
	 * @inheritdoc
	 */
	public function execute() {
		$this->query = "UPDATE{$this->priority}{$this->onError} {$this->table}\nSET {$this->values}\n{$this->where}\n{$this->orderBy}\n{$this->limit}";

		return parent::execute();
	}

	/**
	 * @inheritdoc
	 */
	public function validateValues(array $values) {
		// TODO: Implement validateValues() method.
	}
}