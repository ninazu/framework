<?php

namespace vendor\ninazu\framework\Form;

abstract class Form {

	public function validate() {
		foreach ($this->rules() as $rule) {

		}

		return true;
	}

	abstract public function rules();

	abstract public function fields();
}