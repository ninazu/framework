<?php

namespace vendor\ninazu\framework\Form\Validators;

use vendor\ninazu\framework\Form\BaseValidator;

class UniqueValidator extends BaseValidator {

	protected $callback;

	public function validate($value) {
		$callback = $this->callback;

		return $callback($this->field);
	}

	public function getMessage() {
		return "Field '{$this->field}' must be unique";
	}
}