<?php

namespace vendor\ninazu\framework\Form\Validator;

use vendor\ninazu\framework\Form\BaseValidator;

/**
 * int $min - Minimum Length
 * int $max - Maximum Length
 */
class StringValidator extends BaseValidator {

	protected $min;

	protected $max;

	protected $allowEmpty = false;

	public function validate($value) {
		if (empty($value) && $this->allowEmpty) {
			return true;
		}

		if (!is_string($value) && !is_numeric($value)) {
			$this->message = "Field '{$this->field}' is not a string";

			return false;
		}

		if (isset($this->max) && strlen($value) > $this->max) {
			$this->message = "Field '{$this->field}' more than {$this->max}";

			return false;
		}

		if (isset($this->min) && strlen($value) < $this->min) {
			$this->message = "Field '{$this->field}' lees than {$this->min}";

			return false;
		}

		return true;
	}

	public function getMessage() {
		return $this->message;
	}
}