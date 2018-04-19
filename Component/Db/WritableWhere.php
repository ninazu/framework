<?php

namespace vendor\ninazu\framework\Component\Db;

use ErrorException;

trait WritableWhere {

	protected $where = '';

	/**
	 * @internal
	 *
	 * @param string $condition
	 *
	 * @return $this
	 *
	 * @throws ErrorException
	 */
	public function setWhere($condition) {
		if (!is_string($condition)) {
			throw new ErrorException('Wrong value of condition');
		}

		$condition = trim($condition);

		if (stripos($condition, 'where ') !== 0) {
			$condition = "WHERE {$condition}";
		}

		$this->where = $condition;

		return $this;
	}
}