<?php

namespace vendor\ninazu\framework\Form;

use vendor\ninazu\framework\Form\Validators\Required;
use vendor\ninazu\framework\Helper\Reflector;

abstract class BaseForm {

	protected $data;

	protected $valid;

	protected $required;

	/**
	 * @param array $data
	 *
	 * @return $this
	 */
	public function setRequest(array $data) {
		$this->data = $data;
		$this->valid = null;

		return $this;
	}

	public function wrongRequest() {
		$this->validate();

		return false;
	}

	/**
	 * @return array
	 */
	public function requiredFields() {
		$this->validate();

		return $this->required;
	}

	public function setResponse(array $data) {
		return $this;
	}

	#region Response

	public function emptyResponse() {
		return false;
	}

	public function formatResponse() {
		return [];
	}

	#endregion

	protected function validate() {
		if (is_null($this->valid)) {
			$required = [];
			$valid = true;

			foreach ($this->rules() as $rule) {
				list($fields, $validator) = $rule;

				if (Reflector::isInstanceOf($validator, Required::class)) {
					$required = array_merge($required, $fields);
				}

				if ($valid) {
					$valid = false;
				}
			}

			$this->required = array_unique($required);
			$this->valid = $valid;
		}

		return $this->valid;
	}

	abstract protected function rules();

	abstract protected function response();
}