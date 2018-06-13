<?php

namespace vendor\ninazu\framework\Form;

use ErrorException;
use vendor\ninazu\framework\Component\Db\Interfaces\IConnection;
use vendor\ninazu\framework\Component\Response\IResponse;
use vendor\ninazu\framework\Form\Validator\ChildFormValidator;
use vendor\ninazu\framework\Helper\Reflector;

abstract class BaseMultiForm {

	/**
	 * @var IResponse $response
	 */
	protected $response;

	protected $requestData = [];

	protected $namespace;

	protected $flatRules = [];

	protected $flatRequestData = [];

	protected $childForms = [];

	protected $errors = [];

	public function load(IResponse $response, $requestData) {
		$this->response = $response;
		$this->flatRules = [];
		$this->childForms = [];

		foreach ($this->rules() as $rule) {
			list($fields, $class, $params) = array_pad($rule, 3, []);

			if (!is_string($class) || !Reflector::isInstanceOf($class, BaseValidator::class)) {
				throw new ErrorException("Invalid class of validator '{$class}'");
			}

			foreach ($fields as $field) {
				if (!array_key_exists($field, $requestData)) {
					$requestData[$field] = null;
				}

				$flatField = !is_null($this->namespace) ? "{$this->namespace}.{$field}" : $field;

				if (Reflector::isInstanceOf($class, ChildFormValidator::class)) {
					$this->childForms[$flatField][] = [
						'class' => $class,
						'params' => $params,
					];

					continue;
				}

				$this->flatRules[$flatField][] = [
					'class' => $class,
					'params' => $params,
				];
			}
		}

		$this->flatRequestData = Reflector::toFlatArray($requestData, $this->namespace);
	}

	public function validate() {
		foreach ($this->flatRules as $field => $validators) {
			foreach ($validators as $row) {
				$params = $row['params'];
				$class = $row['class'];

				/**@var BaseValidator $validator */
				$validator = new $class($field, $params, $this->response);
				$value = $this->flatRequestData[$field];

				if (!$validator->validate($value)) {
					$this->addError($field, $validator->getMessage(), $validator->getExtra());
				}
			}
		}

		return !$this->hasErrors();
	}

	/**
	 * @param $field
	 * @param $message
	 * @param null $extra
	 */
	public function addError($field, $message, $extra = null) {
		$data = [
			'field' => $field,
			'message' => $message,
			'extra' => $extra,
		];
		$key = md5(json_encode($data));
		$this->errors[$key] = $data;
	}

	/**
	 * @return array
	 */
	public function getErrors() {
		return array_values($this->errors);
	}

	public function save(IConnection $connection) {
		if (!$this->validate()) {
			$this->response->sendError(IResponse::STATUS_CODE_VALIDATION, $this->getErrors());
		}

		foreach ($this->childForms as $flatField => $params) {
			foreach ($params as $param) {
				if (empty($param['params']['multiply'])) {
					/**@var BaseMultiForm $form */
					$form = new $param['params']['class']();
					$form->namespace = trim("{$this->namespace}.{$flatField}", '.');
					$form->load($this->response, $this->flatRequestData[$flatField]);
					$form->save($connection);
				} else {
					foreach ($this->flatRequestData[$flatField] as $index => $row) {
						/**@var BaseMultiForm $form */
						$form = new $param['params']['class']();
						$form->namespace = trim("{$this->namespace}.{$flatField}.{$index}", '.');
						$form->load($this->response, $row);
						$form->save($connection);
					}
				}
			}
		}
	}

	public function formatResponse() {
		return [];
	}

	/**
	 * @return bool
	 */
	protected function hasErrors() {
		return !empty($this->errors);
	}

	abstract protected function rules();
}