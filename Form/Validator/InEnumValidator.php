<?php

namespace vendor\ninazu\framework\Form\Validator;

use vendor\ninazu\framework\Form\BaseValidator;
use vendor\ninazu\framework\Helper\Reflector;

/**
 * <pre>
 * string   class     Class Name with namespace or ::class,
 * string   prefix    CONST_,
 * </pre>
 */
class InEnumValidator extends BaseValidator {

	protected $class;

	protected $prefix;

	private $constGroup;

	public function validate($value, &$newValue) {
		$this->constGroup = Reflector::getConstantGroup($this->class, $this->prefix)->getData();

		return array_key_exists($value, $this->constGroup);
	}

	public function getMessage() {
		$className = Reflector::getClassShortName($this->class);
		$range = implode(', ', $this->constGroup);

		return "Field '{$this->field}' not in range ($range). Please use {$className}::{$this->prefix}* const";
	}
}