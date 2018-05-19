<?php

namespace vendor\ninazu\framework\Form\Validators;

use vendor\ninazu\framework\Form\BaseValidator;
use vendor\ninazu\framework\Helper\Reflector;

class InEnumValidator extends BaseValidator {

	protected $enum;

	private $constGroup;

	private $extra = [];

	public function validate($value) {
		$this->constGroup = Reflector::getConstantGroup($this->enum['class'], $this->enum['prefix'])->getData();

		return array_key_exists($value, $this->constGroup);
	}

	public function getMessage() {
		$className = Reflector::getClassShortName($this->enum['class']);
		$range = implode(', ', $this->constGroup);

		return "Field '{$this->field}' not in range ($range). Please use {$className}::{$this->enum['prefix']}* const";
	}

	public function getExtra() {
		return $this->extra;
	}
}