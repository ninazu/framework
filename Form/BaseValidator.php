<?php

namespace vendor\ninazu\framework\Form;

use vendor\ninazu\framework\Core\BaseConfigurator;

abstract class BaseValidator extends BaseConfigurator {

	protected $field;

	public function __construct($field, $params) {
		$this->field = $field;
		$this->fillFromConfig($params);
		$this->init();
	}

	public function init() {
		//TODO Validate Validator
	}

	public function getExtra() {
		return [];
	}

	abstract public function validate($value);

	abstract public function getMessage();
}