<?php

namespace vendor\ninazu\framework\Component\Db;

use ErrorException;
use vendor\ninazu\framework\Component\Db\Interfaces\IDelete;
use vendor\ninazu\framework\Component\Db\Interfaces\IDeleteResult;
use vendor\ninazu\framework\Helper\Formatter;

class DeleteQuery extends WritableQuery implements IDelete, IDeleteResult {

	use WritableWhere;
	use OrderAndLimit;

	private $partitions = [];

	/**
	 * @inheritdoc
	 */
	public function count() {
		// TODO: Implement count() method.
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

	public function priority($scenario) {
		if (!in_array($scenario, [self::PRIORITY_LOW])) {
			throw new ErrorException('Wrong value of priority. Please use DeleteQuery::PRIORITY_* constants');
		}

		$this->priority = $scenario;

		return $this;
	}

	protected function prepareSql() {
		$query = "DELETE{$this->priority}{$this->onError} FROM {$this->table}\n{$this->where}{$this->orderBy}{$this->limit}";

		$this->query = Formatter::removeLeftTabs($query);

		return [
			[
				'bindsString' => $this->bindsString,
				'query' => $this->query,
			],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function partitions(array $partitions) {
		$this->partitions = implode(',', $partitions);

		return $this;
	}
}