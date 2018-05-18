<?php

namespace vendor\ninazu\framework\Form\Validators;

use vendor\ninazu\framework\Form\Validator;

class Required extends Validator {

	public function validate($value) {
		return !empty($value);
	}
}