<?php

namespace vendor\ninazu\framework\Form\Validators;

use vendor\ninazu\framework\Form\BaseValidator;

class RequiredValidator extends BaseValidator {

	public function validate($value) {
		return !empty($value);
	}

	public function getMessage() {
		return "Field '{$this->field}' are required";
	}
}