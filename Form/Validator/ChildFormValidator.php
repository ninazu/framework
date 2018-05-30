<?php

namespace vendor\ninazu\framework\Form\Validator;

use ErrorException;
use vendor\ninazu\framework\Form\BaseForm;
use vendor\ninazu\framework\Form\BaseValidator;
use vendor\ninazu\framework\Helper\Reflector;

class ChildFormValidator extends BaseValidator {

	protected $class;

	protected $multiply = false;

	protected $hasDependency = true;

	private $message;

	public function validate($value) {
		$isAssoc = false;

		if (Reflector::isAssocArray($value)) {
			$isAssoc = true;
			$value = [$value];
		}

		if (!Reflector::isInstanceOf($this->class, BaseForm::class)) {
			throw new ErrorException('ChildForm class must be instance of BaseValidator');
		}

		$class = $this->class;

		foreach ($value as $index => $row) {
			/**@var BaseForm $class */

//			$class = new $class();
//			$required = $class->getRequired();
//			$missing = array_diff($required, array_keys($row));
//
//			if ($missing) {
//				$fields = implode(', ', $missing);
//				$atIndex = "";
//
//				if ($isAssoc) {
//					$atIndex = " at index [{$index}]";
//				}
//
//				$message = "Fields ({$fields}){$atIndex} are required";
//
//				$this->response->sendError(Response::STATUS_CODE_BAD_REQUEST, [$this->field], [
//					$message,
//				]);
//			}
		}

		return true;
	}

	public function getMessage() {
		return $this->message;
	}
}