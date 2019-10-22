<?php

namespace vendor\ninazu\framework\Form;

use vendor\ninazu\framework\Core\BaseConfigurator;

abstract class BaseValidator extends BaseConfigurator {

	protected $field;

	protected $message;

	protected $extra = [];

	public function __construct($field, $params) {
		$this->field = $field;
		$this->fillFromConfig($params);
		$this->init();
	}

	protected function init() {
		return;
	}

	public function setExtra(array $extra) {
		$this->extra = $extra;
	}

	public function getExtra() {
		return $this->extra;
	}

	public function setMessage($message) {
		$this->message = $message;
	}

	public function setField($field) {
		$this->field = $field;
	}

	public function getField() {
		return $this->field;
	}

	abstract public function validate($value, &$newValue);

	abstract public function getMessage();
}