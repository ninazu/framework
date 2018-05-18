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

	#region Response

	public function load(array $data) {
		$this->responseData = $data;

		$fields = $this->response();

		return $this;
	}

	/**
	 * @return bool
	 */
	public function emptyResponse() {
		return empty($this->responseData);
	}

	/**
	 * @return array
	 */
	public function formatResponse() {
		return $this->responseData;
	}

	/**
	 * @param $name
	 * @return mixed|null
	 */
	public function getResponse($name) {
		return isset($this->responseData[$name]) ? $this->responseData[$name] : null;
	}

	/**
	 * @param $name
	 * @param $value
	 */
	public function setResponse($name, $value) {
		$this->requestData[$name] = $value;
	}

	#endregion

	#region Request

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

	/**
	 * @return bool
	 */
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

	/**
	 * @param $name
	 *
	 * @return mixed|null
	 */
	public function getRequest($name) {
		return isset($this->requestData[$name]) ? $this->requestData[$name] : null;
	}

	/**
	 * @return array
	 */
	public function requiredFields() {
		return $this->required;
	}

	#endregion

	abstract protected function rules();

	abstract protected function response();
}