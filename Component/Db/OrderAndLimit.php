<?php

namespace vendor\ninazu\framework\Component\Db;

use RuntimeException;

trait OrderAndLimit {

	private $limit;

	private $orderBy;

	/**
	 * @inheritdoc
	 */
	public function orderBy(array $sequence) {
		foreach ($sequence as $expression) {
			if (!$expression instanceof Expression) {
				throw new RuntimeException('Order by must be array of expressions');
			}
		}

		$this->orderBy = "\nORDER BY " . implode(', ', $sequence);

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function limit($count) {
		if (!is_int($count) && !is_numeric($count)) {
			throw new RuntimeException('Wrong value for limit');
		}

		$this->limit = "\nLIMIT {$count}";

		return $this;
	}
}