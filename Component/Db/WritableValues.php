<?php

namespace vendor\ninazu\framework\Component\Db;

use ErrorException;

trait WritableValues {

	protected $values;

	/**
	 * @internal
	 *
	 * @param array $values
	 * @param $validate
	 *
	 * @return $this
	 *
	 * @throws ErrorException
	 */
	public function setValues($values, $validate) {
		if (empty($values)) {
			throw new ErrorException('Empty values');
		}

		if ($validate) {
			if ($this->validateValues($values)) {
				$this->values = $values;
			}
		} elseif ($values instanceof Expression) {
			$this->values = $values;
		} else {
			throw new ErrorException('Wrong values. Expected ArrayOfArray or Expression');
		}

		return $this;
	}

	/**
	 * @param array $values
	 *
	 * @return bool
	 */
	abstract protected function validateValues(array $values);
}