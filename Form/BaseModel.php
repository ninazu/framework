<?php

namespace vendor\ninazu\framework\Form;

use ErrorException;
use Exception;
use vendor\ninazu\framework\Component\Db\Interfaces\IConnection;
use vendor\ninazu\framework\Component\Db\Interfaces\ITransaction;
use vendor\ninazu\framework\Component\Response\IResponse;
use vendor\ninazu\framework\Component\Response\Response;
use vendor\ninazu\framework\Form\Validator\ChildFormValidator;
use vendor\ninazu\framework\Form\Validator\RequiredValidator;
use vendor\ninazu\framework\Helper\Reflector;

abstract class BaseModel {

	const ON_WRITE = 1;

	const ON_READ = 2;

	protected $namespace = null;

	/**@var Response $response */
	protected $response = null;

	protected $responseData = [];

	protected $flatRequestData = [];

	protected $flatRules = [];

	protected $valid = null;

	protected $requiredFields = [];

	protected $transaction = null;

	protected $attributes = [];

	protected $errors = [];

	protected $scenario = self::ON_WRITE;

	public function setScenario($scenario) {
		$list = Reflector::getConstantGroup(static::class, 'ON_');

		return $this;
	}

	/**@var BaseModel $parentForm */
	protected $parentForm = null;

	public function __construct(BaseModel $parentForm = null) {
		$this->parentForm = $parentForm;
	}

	/**
	 * @return IConnection|ITransaction
	 */
	public function getTransaction() {
		return $this->transaction;
	}

	public function trySaveInside(callable $function) {
		if (!$this->parentForm) {
			throw new ErrorException('trySaveInside without parentForm');
		}

		$this->load($this->parentForm->response, $this->parentForm->getAttributes());
		$this->trySave($this->parentForm->getTransaction(), $function);

		return $this;
	}

	public function setConnection(IConnection $connection) {
		$this->transaction = $connection;

		return $this;
	}

	/**
	 * @param IConnection $connection
	 * @param callable $function
	 *
	 * @return BaseModel
	 *
	 * @throws Exception
	 */
	public function trySave(IConnection $connection, callable $function) {
		$this->transaction = $connection->beginTransaction();

		try {
			if (!$this->validate()) {
				$this->setParentFormError();

				return $this;
			}

			$function($this);

			if ($this->hasErrors()) {
				$this->transaction->rollback();
				$this->setParentFormError();

				return $this;
			}

			$this->transaction->commit();
		} catch (Exception $exception) {
			$rollBackMessage = null;

			if (!$this->hasErrors() && is_null($this->transaction->getRollBackMessage())) {
				$this->addError(null, $exception->getMessage());
				$rollBackMessage = $exception->getMessage();
			}

			$this->transaction->rollback($rollBackMessage);
			$this->setParentFormError();

			throw $exception;
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
	 * @param $name
	 * @return mixed|null
	 */
	public function getResponseData($name) {
		return isset($this->responseData[$name]) ? $this->responseData[$name] : null;
	}

	/**
	 * @return array
	 */
	public function getAttributes() {
		return $this->attributes;
	}

	/**
	 * @return array
	 */
	public function getRequiredFields() {
		return $this->requiredFields;
	}

	/**
	 * @return array
	 */
	public function getFlatRules() {
		return $this->flatRules;
	}

	/**
	 * @return bool|null
	 *
	 * @throws Exception
	 */
	public function validate() {
		if (is_null($this->valid)) {
			/**@var BaseValidator $validator */
			$delayedValidators = [];

			foreach ($this->flatRules as $field => $validators) {
				foreach ($validators as $class => $params) {
					if (isset($params['on'])) {
						if (is_array($params['on'])) {
							if (!in_array($this->scenario, $params['on'])) {
								continue;
							}
						} else if (is_int($params['on'])) {
							continue;
						} else {
							throw new Exception("'on' params of Validator must be Array");
						}
					}

					$validator = new $class($field, $params, $this->response);

					if ($validator instanceof RequiredValidator) {
						$this->requiredFields[] = $field;
					}

					$value = $this->flatRequestData[$field];
					$this->attributes[$field] = $value;

					if ($validator->hasDependency()) {
						$delayedValidators[$class]['validator'] = $validator;
						$delayedValidators[$class]['fields'][$field] = $value;

						continue;
					}

					if (!$validator->validate($value)) {
						$this->addError($field, $validator->getMessage(), $validator->getExtra());
					}
				}
			}

			if (empty($this->errors)) {
				foreach ($delayedValidators as $job) {
					$validator = $job['validator'];

					foreach ($job['fields'] as $field => $value) {
						if (isset($this->errors[$field])) {
							continue;
						}

						if (!$validator->validate($value)) {
							$this->addError($field, $validator->getMessage(), $validator->getExtra());

							continue;
						}
					}
				}
			}

			$missing = array_diff($this->requiredFields, array_keys($this->flatRequestData));
			$this->valid = empty($this->errors) && empty($missing);

			if ($missing) {
				$this->response->sendError(Response::STATUS_CODE_BAD_REQUEST, array_values($missing));
			}

			if (!$this->valid) {
				$this->response->sendError(Response::STATUS_CODE_VALIDATION, $this->getErrors());
			}

			$this->afterValidate();
		}

		return $this->valid;
	}

	/**
	 * @param IResponse $response
	 * @param array $requestData
	 * @throws ErrorException
	 */
	public function load(IResponse $response, array $requestData) {
		$this->response = $response;
		$this->flatRequestData = Reflector::toFlatArray($requestData);

		if ($rules = $this->rules()) {
			foreach ($rules as $rule) {
				list($fields, $validator, $params) = array_pad($rule, 3, []);

				if (!is_string($validator) || !Reflector::isInstanceOf($validator, BaseValidator::class)) {
					throw new ErrorException("Invalid class of validator '{$validator}'");
				}

				foreach ($fields as $field) {
					if (!array_key_exists($field, $requestData)) {
						//TODO Stop
						if (Reflector::isInstanceOf($validator, RequiredValidator::class)) {
							$requestData[$field] = null;
						} else {
							continue;
						}
					}

					/**@var BaseModel $model */
					$model = null;
					$flatField = !is_null($this->namespace) ? "{$this->namespace}.{$field}" : $field;

					if (Reflector::isInstanceOf($validator, ChildFormValidator::class)) {
						if (empty($params['multiply'])) {
							$this->mergeSubRules($params['class'], $field, $requestData[$field]);
						} else {
							foreach ($requestData[$field] as $index => $row) {
								$this->mergeSubRules($params['class'], "{$field}.{$index}", $row);
							}
						}

						continue;
					}

					$this->flatRules[$flatField][$validator] = $params;
				}
			}
		}

		$this->valid = null;
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
		$this->valid = false;
	}

	public function getErrors() {
		return array_values($this->errors);
	}

	public function formatResponse() {
		if (!$this->valid) {
			if ($this->parentForm) {
				return $this->getErrors();
			}

			$this->response->sendError(Response::STATUS_CODE_VALIDATION, $this->getErrors());
		}

		return $this->responseData;
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

	protected function afterValidate() {
		return;
	}

	/**
	 * @return bool
	 */
	protected function hasErrors() {
		return !empty($this->errors);
	}

	abstract protected function rules();

	protected function postProcessors() {
		return [];
	}

	private function setParentFormError() {
		if (!$this->parentForm) {
			return;
		}

		$this->parentForm->errors = array_merge($this->errors, $this->parentForm->errors);
		$this->parentForm->valid = !$this->parentForm->hasErrors();
	}

	private function mergeSubRules($class, $suffix, $data) {
		/**@var BaseModel $model */
		$model = new $class();
		$model->namespace = !is_null($this->namespace) ? "{$this->namespace}.{$suffix}" : $suffix;
		$model->load($this->response, $data);

		$this->flatRules = array_merge($this->flatRules, $model->getFlatRules());
	}
}