<?php

namespace vendor\ninazu\framework\Form\Validator;

use vendor\ninazu\framework\Form\BaseValidator;

/**
 * <pre>
 *
 * </pre>
 */
class EmailValidator extends BaseValidator {

	public function validate($value) {
		return filter_var($value, FILTER_VALIDATE_EMAIL);
	}

	public function getMessage() {
		return "Field '{$this->field}' is not a valid email address";
	}
}