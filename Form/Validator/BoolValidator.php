<?php

namespace vendor\ninazu\framework\Form\Validator;

use vendor\ninazu\framework\Form\BaseValidator;

class BoolValidator extends BaseValidator {

	public function validate(&$value) {
		return (bool)$value === $value;
	}

	public function getMessage() {
		return "Field '{$this->field}' must be boolean";
	}
}