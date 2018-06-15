<?php

namespace vendor\ninazu\framework\Form\Validator;

/**
 * callable $callback($field)
 * bool $hasDependency
 */
class UniqueValidator extends CustomValidator {

	public function getMessage() {
		if ($this->message) {
			return $this->message;
		}

		return "Field '{$this->field}' must be unique";
	}
}