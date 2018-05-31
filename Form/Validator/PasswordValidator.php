<?php

namespace vendor\ninazu\framework\Form\Validator;

/**
 * int $min - minimum Length
 */

class PasswordValidator extends StringValidator {

	public function init() {
		parent::init();

		$this->min = 8;
	}

	public function validate($value) {
		if (!preg_match("/^\\S*(?=\\S{{$this->min},})(?=\\S*[a-z])(?=\\S*[A-Z])(?=\\S*[\\d])(?=\S*[\W])\\S*\$/", $value)) {
			$this->message = "Password must contains Number, UpperCase, LowerCase and SpecialChar";

			return false;
		}

		return parent::validate($value);
	}
}