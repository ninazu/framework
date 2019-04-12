<?php

namespace vendor\ninazu\framework\Component\Db;

use RuntimeException;

trait WritableWhere {

	protected $where = '';

	/**
	 * @internal
	 *
	 * @param string $condition
	 *
	 * @return $this
	 *
	 * @throws RuntimeException
	 */
	public function setWhere($condition) {
		if (!is_string($condition)) {
			throw new RuntimeException('Wrong value of condition');
		}

		$condition = trim($condition);

		if (stripos($condition, 'where ') !== 0) {
			$condition = "WHERE {$condition}";
		}

		$this->where = $condition;

		return $this;
	}
}