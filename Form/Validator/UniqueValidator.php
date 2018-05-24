<?php

namespace vendor\ninazu\framework\Form\Validator;

use ErrorException;
use vendor\ninazu\framework\Form\BaseValidator;

class UniqueValidator extends BaseValidator {

	protected $callback;

	protected $message;

	public function validate($value) {
		if (!is_callable($this->callback)) {
			throw new ErrorException('Callback must be callable');
		}

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