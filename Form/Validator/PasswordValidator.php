<?php

namespace vendor\ninazu\framework\Form\Validator;

/**
 * int $min - minimum Length
 */
class PasswordValidator extends StringValidator {

	protected $allowEmpty = false;

	public function init() {
		parent::init();

		$this->min = 8;
	}

	public function validate($value, &$newValue) {
		if (!$parent = parent::validate($value, $newValue)) {
			return false;
		}

		if (empty($value) && $this->allowEmpty) {
			return true;
		}

		if (!preg_match("/^\\S*(?=\\S{{$this->min},})(?=\\S*[a-z])(?=\\S*[A-Z])(?=\\S*[\\d])(?=\S*[\W])\\S*\$/", $value)) {
			$this->message = "Password must contains Number, UpperCase, LowerCase and SpecialChar";

			return false;
		}

		return $parent;
	}
}