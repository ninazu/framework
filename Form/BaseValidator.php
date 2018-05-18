<?php

namespace vendor\ninazu\framework\Form;

abstract class BaseValidator {

	protected $field;

	protected $params;

	public function __construct($field, $params) {
		$this->field = $field;
		$this->params = $params;
	}

	abstract public function validate($value);

	abstract public function getMessage();
}