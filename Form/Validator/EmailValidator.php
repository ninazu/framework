<?php

namespace vendor\ninazu\framework\Form\Validator;

class EmailValidator extends StringValidator {

	protected $min = 6;

	protected $max = 254;

	public function validate($value, &$newValue) {
		if ($this->allowEmpty && empty($value)) {
			return true;
		}

		return filter_var($value, FILTER_VALIDATE_EMAIL);
	}

	public function getMessage() {
		return "Field '{$this->field}' is not a valid email address";
	}
}