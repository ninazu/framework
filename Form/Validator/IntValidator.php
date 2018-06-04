<?php

namespace vendor\ninazu\framework\Form\Validator;

use vendor\ninazu\framework\Form\BaseValidator;

class IntValidator extends BaseValidator {

	public function validate($value) {
		return (int)($value) == $value;
	}

	public function getMessage() {
		return "Field '{$this->field}' must be integer";
	}
}