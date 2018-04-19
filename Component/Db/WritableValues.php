<?php

namespace vendor\ninazu\framework\Component\Db;

use ErrorException;

trait WritableValues {

	protected $values;

	/**
	 * @internal
	 *
	 * @param array $values
	 *
	 * @return WritableValues
	 *
	 * @throws ErrorException
	 */
	public function setValues(array $values) {
		if (empty($values)) {
			throw new ErrorException('Empty values');
		}

		if ($this->validateValues($values)) {
			$this->values = $values;
		}

		return $this;
	}

	/**
	 * @param array $values
	 *
	 * @return bool
	 */
	abstract function validateValues(array $values);
}