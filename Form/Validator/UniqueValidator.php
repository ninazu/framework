<?php

namespace vendor\ninazu\framework\Form\Validator;

use ErrorException;
use vendor\ninazu\framework\Form\BaseValidator;

/**
 * callable $callback($field)
 * bool $hasDependency
 */
class UniqueValidator extends BaseValidator {

	/**
	 * @var \Closure $callback
	 */
	protected $callback;

	private $value;

	protected function init() {
		$this->hasDependency = true;

		if (!is_callable($this->callback)) {
			throw new ErrorException('Callback must be callable');
		}

		$this->callback->bindTo($this);
	}

	public function validate($value) {
		$callback = $this->callback;
		$this->value = $value;

		return !$callback($this->field);
	}

	public function getMessage() {
		if ($this->message) {
			return $this->message;
		}

		return "Field '{$this->field}' must be unique";
	}

	public function getExtra() {
		return [
			$this->field => $this->value,
		];
	}
}