<?php

namespace vendor\ninazu\framework\Form\Validator;

use vendor\ninazu\framework\Form\BaseValidator;

/**
 * bool $allowEmpty
 */
class RequiredValidator extends BaseValidator {

	protected $allowEmpty = false;

	protected $onSave = false;

	public function applyOnSave() {
		return $this->onSave;
	}

	public function validate($value) {
		if ($this->allowEmpty) {
			return isset($value);
		}

		return !empty($value);
	}

	public function getMessage() {
		return "Field '{$this->field}' are required";
	}
}