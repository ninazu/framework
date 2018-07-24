<?php

namespace vendor\ninazu\framework\Form\Validator;

use ErrorException;
use vendor\ninazu\framework\Form\BaseValidator;

class CustomValidator extends BaseValidator {

	/**
	 * @var \Closure $callback
	 */
	protected $callback;

	protected $value;

	public function validate($value) {
		$callback = $this->callback;
		$this->setValue($value);

		return $callback($this);
	}

	public function getValue() {
		return $this->value;
	}

	public function setValue($value) {
		$this->value = $value;
	}

	public function getMessage() {
		if ($this->message) {
			return $this->message;
		}

		return "Field '{$this->field}' failed validation";
	}

	public function getExtra() {
		if ($this->extra) {
			return $this->extra;
		}

		return [
			$this->field => $this->value,
		];
	}

	protected function init() {
		$this->hasDependency = true;

		if (!is_callable($this->callback)) {
			throw new ErrorException('Callback must be callable');
		}

		$this->callback->bindTo($this);
	}
}