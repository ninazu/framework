<?php

namespace vendor\ninazu\framework\Form\Validator;

use vendor\ninazu\framework\Form\BaseValidator;

class UniqueValidator extends BaseValidator {

	protected $callback;

	protected $message;

	protected $target;

	public function validate($value) {
		$callback = $this->callback;

		return $callback($this->field);
	}

	public function getMessage() {
		if ($this->message) {
			return $this->message;
		}

		return "Field '{$this->field}' must be unique";
	}
}