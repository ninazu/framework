<?php

namespace vendor\ninazu\framework\Form;

use ErrorException;
use vendor\ninazu\framework\Component\Db\Interfaces\IConnection;
use vendor\ninazu\framework\Component\Db\Interfaces\ITransaction;
use vendor\ninazu\framework\Component\Response\IResponse;
use vendor\ninazu\framework\Form\Validator\ChildFormValidator;
use vendor\ninazu\framework\Helper\Reflector;

abstract class BaseModel {

	const ON_WRITE = 'write';

	const ON_READ = 'read';

	protected $scenario = self::ON_WRITE;

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

	protected $parentForm;

	protected $transaction;

	protected $attributes = [];

	protected $responseData = [];

	public function __construct(BaseModel $parentForm = null) {
		$this->parentForm = $parentForm;
	}

	public function setScenario($scenario) {
		$list = Reflector::getConstantGroup(static::class, 'ON_')->getData();

		if (!array_key_exists($scenario, $list)) {
			throw new ErrorException('Wrong scenario, please declare CONST before use');
		}

		return $this;
	}

	/**
	 * @param $name
	 * @return mixed|null
	 */
	public function getAttribute($name) {
		return isset($this->attributes[$name]) ? $this->attributes[$name] : null;
	}

	/**
	 * @return array
	 */
	public function getAttributes() {
		return $this->attributes;
	}

	/**
	 * @return IConnection|ITransaction
	 */
	public function getTransaction() {
		return $this->transaction;
	}

	public function setConnection(IConnection $connection) {
		$this->transaction = $connection;
	}

	/**
	 * @param IResponse $response
	 * @param array $requestData
	 * @throws ErrorException
	 */
	public function load(IResponse $response, array $requestData) {
		$this->response = $response;
		$this->flatRules = [];
		$this->childForms = [];
		$this->flatRequestData = [];

		if (!$rules = $this->rules()) {
			return;
		}

		foreach ($rules as $rule) {
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

	/**
	 * @return bool
	 * @throws ErrorException
	 */
	public function validate() {
		$this->beforeValidate();

		foreach ($this->flatRules as $field => $validators) {
			foreach ($validators as $row) {
				$params = $row['params'];
				$class = $row['class'];

				if (isset($params['on'])) {
					if (is_array($params['on'])) {
						if (!in_array($this->scenario, $params['on'])) {
							continue;
						}
					} else if (is_int($params['on'])) {
						continue;
					} else {
						throw new ErrorException("'on' params of Validator must be Array");
					}
				}

				/**@var BaseValidator $validator */
				$validator = new $class($field, $params, $this->response);
				$value = $this->flatRequestData[$field];
				$this->attributes[$this->removeNameSpace($field)] = $value;

				if (!$validator->validate($value)) {
					$this->addError($field, $validator->getMessage(), $validator->getExtra());
				}
			}
		}

		$this->afterValidate();

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

	/**
	 * @param IConnection $connection
	 * @return array
	 * @throws ErrorException
	 */
	public function trySave(IConnection $connection) {
		$this->transaction = $connection;

		if (!$this->validate()) {
			$this->response->sendError(IResponse::STATUS_CODE_VALIDATION, $this->getErrors());
		}

		$data = $this->save();
		$namespace = $this->getSafeNamespace();
		$response = $data;

		foreach ($this->childForms as $flatField => $params) {
			foreach ($params as $param) {
				if (empty($param['params']['multiply'])) {
					$this->processChildForm($response, $namespace, $param['params']['class'], $flatField, $this->flatRequestData[$flatField], $data);
				} else {
					foreach ($this->flatRequestData[$flatField] as $index => $row) {
						$this->processChildForm($response, $namespace, $param['params']['class'], "{$flatField}.{$index}", $row, $data);
					}
				}
			}
		}

		$this->createResponse($response);

		return $this->responseData;
	}

	private function processChildForm(&$response, $namespace, $class, $field, $row, $data) {
		/**@var BaseModel $form */
		$form = new $class();
		$form->namespace = "{$namespace}{$field}";
		$form->load($this->response, $this->mergeWithNamespace((array)$row, $data));
		$responseForm = $form->trySave($this->getTransaction());

		if (is_array($responseForm)) {
			foreach ($responseForm as $key => $value) {
				Reflector::flatKeyToLink($response, "{$form->namespace}.{$key}", $value);
			}
		} else {
			Reflector::flatKeyToLink($response, "{$form->namespace}", $responseForm);
		}
	}

	public function createResponse($data) {
		if ($processors = $this->postProcessors()) {
			foreach ($processors as $rule) {
				list($fields, $class, $params) = array_pad($rule, 3, []);;

				if (!is_string($class) || !Reflector::isInstanceOf($class, BaseProcessor::class)) {
					throw new ErrorException("Invalid PostProcessor '{$class}'");
				}

				/**@var BaseProcessor $processor */
				$processor = new $class($fields, $params);
				$processor->execute($data);
			}
		}

		$this->responseData = $data;

		if ($this->parentForm) {
			$this->parentForm->createResponse($data);
		}
	}

	public function setAttribute($name, $value) {
		$namespace = $this->getSafeNamespace();

		$this->attributes[$name] = $value;
		$this->requestData[$name] = $value;
		$this->flatRequestData["{$namespace}{$name}"] = $value;
	}

	public function formatResponse() {
		if ($this->hasErrors()) {
			if ($this->parentForm) {
				return $this->getErrors();
			}

			$this->response->sendError(IResponse::STATUS_CODE_VALIDATION, $this->getErrors());
		}

		return $this->responseData;
	}

	/**
	 * @param $name
	 * @return mixed|null
	 */
	public function getResponseData($name) {
		return isset($this->responseData[$name]) ? $this->responseData[$name] : null;
	}

	protected function mergeWithNamespace(array $params, array $data) {
		$tmp = [];
		$namespace = $this->getSafeNamespace();

		foreach ($data as $key => $value) {
			//if (array_key_exists("{$namespace}{$key}", $this->flatRules)) {
			$tmp["{$namespace}{$key}"] = $value;
			//}
		}

		return array_replace_recursive($tmp, $params);
	}

	protected function beforeValidate() {
		return;
	}

	protected function afterValidate() {
		return;
	}

	protected function postProcessors() {
		return [];
	}

	/**
	 * @return bool
	 */
	protected function hasErrors() {
		return !empty($this->errors);
	}

	abstract protected function rules();

	abstract protected function save();

	private function removeNameSpace($name) {
		return str_replace("{$this->namespace}.", '', $name);
	}

	private function getSafeNamespace() {
		return ltrim("{$this->namespace}.", '.');
	}
}