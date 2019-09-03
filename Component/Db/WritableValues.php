<?php

namespace vendor\ninazu\framework\Component\Db;

use RuntimeException;

trait WritableValues {

	protected $values;

	/**
	 * @param array $values
	 * @param $validate
	 *
	 * @return $this
	 *
	 * @internal
	 *
	 */
	public function setValues($values, $validate) {
		if (empty($values)) {
			return $this;
		}

		if ($validate) {
			if ($this->validateValues($values)) {
				$this->values = $values;
			}
		} elseif ($values instanceof Expression) {
			$this->values = $values;
		} else {
			throw new RuntimeException('Wrong values. Expected ArrayOfArray or Expression');
		}

		return $this;
	}

	/**
	 * @param array $values
	 *
	 * @return bool
	 */
	abstract protected function validateValues(array &$values);
}