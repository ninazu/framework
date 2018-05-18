<?php

namespace vendor\ninazu\framework\Form;

use vendor\ninazu\framework\Form\Validators\Required;
use vendor\ninazu\framework\Helper\Reflector;

abstract class BaseForm {

	private $requestData = [];

	private $responseData = [];

	private $valid = null;

	private $required = [];

	protected $errors = [];

	/**
	 * @param array $data
	 *
	 * @return bool
	 */
	public function validate(array $data) {
		$this->valid = null;
		$this->requestData = $data;

		return $this->processRequest();
	}

	public function getResponse($name) {
		return isset($this->responseData[$name]) ? $this->responseData[$name] : null;
	}

	public function setResponse($name, $value) {
		$this->requestData[$name] = $value;
	}

	public function getRequest($name) {
		return isset($this->requestData[$name]) ? $this->requestData[$name] : null;
	}

	/**
	 * @return array
	 */
	public function requiredFields() {
		return $this->required;
	}

	public function load(array $data) {
		$this->responseData = $data;

		$fields = $this->response();

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

	protected function processRequest() {
		if (is_null($this->valid)) {
			$required = [];
			$validators = [];

			foreach ($this->rules() as $rule) {
				list($fields, $validator, $params) = array_pad($rule, 3, []);

				if (Reflector::isInstanceOf($validator, Required::class)) {
					$required = array_merge($required, $fields);
				}

				foreach ($fields as $field) {
					$validators[$field][$validator] = $params;
				}
			}

			foreach ($this->requestData as $field => $value) {
				if (!isset($validators[$field])) {
					continue;
				}

				foreach ($validators[$field] as $class => $params) {
					/**@var BaseValidator $validator */
					$validator = new $class($field, $params);

					if (!$validator->validate($value)) {
						$this->errors[$field] = $validator->getMessage();

						continue;
					}
				}
			}

			$this->required = array_unique($required);
			$this->valid = empty($this->errors);
		}

		return !$this->valid;
	}

	abstract protected function rules();

	abstract protected function response();
}