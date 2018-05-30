<?php

namespace vendor\ninazu\framework\Form;

use ErrorException;
use Exception;
use vendor\ninazu\framework\Component\Db\Interfaces\IConnection;
use vendor\ninazu\framework\Component\Response\IResponse;
use vendor\ninazu\framework\Component\Response\Response;
use vendor\ninazu\framework\Form\Validator\ChildFormValidator;
use vendor\ninazu\framework\Helper\Reflector;

abstract class BaseForm {

	private $requestData = [];

	private $responseData = [];

	private $valid = null;

	private $required = [];

	private $validators = [];

	private $childValidators = [];

	/**@var Response $response */
	private $response;

	private $transaction;

	public $namespace;

	protected $attributes = [];

	private $errors = [];

	public function getTransaction() {
		return $this->transaction;
	}

	public function trySave(IConnection $connection, callable $function) {
		$this->transaction = $connection->beginTransaction();

		try {
			if (!$this->validate()) {
				return false;
			}

			if (!$function($this)) {
				throw new Exception('Form return FALSE without errors');
			}

			$this->transaction->commit();

			return true;
		} catch (Exception $exception) {
			$rollBackMessage = null;

			if (!$this->hasErrors() && is_null($this->transaction->getRollBackMessage())) {
				$this->addError(null, $exception->getMessage());
				$rollBackMessage = $exception->getMessage();
			}

			$this->transaction->rollback($rollBackMessage);

			return false;
		}
	}

	public function getAttributes() {
		return $this->attributes;
	}

	public function getRequired() {
		return $this->required;
	}

	public function __construct() {
		if ($rules = $this->rules()) {
			foreach ($rules as $rule) {
				list($fields, $validator, $params) = array_pad($rule, 3, []);

				if (!is_string($validator) || !Reflector::isInstanceOf($validator, BaseValidator::class)) {
					throw new ErrorException("Invalid class of validator '{$validator}'");
				}

				if (Reflector::isInstanceOf($validator, ChildFormValidator::class)) {
					foreach ($fields as $field) {
						$this->childValidators[$validator][$field] = $params;
					}
				} else {
					foreach ($fields as $field) {
						$this->validators[$validator][$field] = $params;
					}
				}
			}
		}
		//if (Reflector::isInstanceOf($validator, RequiredValidator::class)) {
		//	$required = array_merge($required, $fields);
		//}
		//$this->required = array_unique($required);
	}

	public function validate() {
		if (is_null($this->valid)) {
			/**@var BaseValidator $validator */

			foreach ($this->validators as $class => $fields) {
				foreach ($fields as $field => $params) {
					if (!array_key_exists($field, $this->requestData[$field]) && isset($this->errors[$field])) {
						continue;
					}

					$value = $this->requestData[$field];
					//"{$this->namespace}{$field}"

					$validator = new $class($field, $params, $this->response);

					if ($validator->hasDependency()) {
						$delayedValidator[] = [
							'validator' => $validator,
							'value' => $value,
						];

						continue;
					}

					$validator->validate($value);
				}
			}

			foreach ($delayedValidator as $job) {
				$validator = $job['validator'];
				$validator->validate($job['value']);
			}

//			foreach ($this->requestData as $field => $value) {
//				if (!isset($this->validators[$field])) {
//					continue;
//				}
//
//				foreach ($this->validators[$field] as $class => $params) {
//					/**@var BaseValidator $validator */
//					$validator = new $class($field, $params, $this->response);
//
//					if (!$validator->validate($value)) {
//						$this->addError($field, $validator->getMessage(), $validator->getExtra());
//
//						continue;
//					}
//
//					$this->attributes[$field] = $value;
//				}
//			}
//
//			$missing = array_diff($this->required, array_keys($this->requestData));
//			$this->valid = empty($this->errors) && empty($missing);
//
//			//TODO Force send response
//			if ($missing) {
//				$this->response->sendError(Response::STATUS_CODE_BAD_REQUEST, array_values($missing));
//			}
//
//			//TODO Force send response
//			if (!$this->valid) {
//				$this->response->sendError(Response::STATUS_CODE_VALIDATION, $this->errors);
//			}
		}

		return $this->valid;
	}

	public function formatResponse() {
		return $this->responseData;
	}

	protected function addError($field, $message, $extra = null) {
		$this->errors[] = [
			'field' => "{$this->namespace}{$field}",
			'message' => $message,
			'extra' => $extra,
		];
	}

	protected function hasErrors() {
		return !empty($this->errors);
	}

	public function createResponse($data) {
		if (!is_array($data) || empty($data)) {
			return true;
		}

		if ($processors = $this->postProcessors()) {
			foreach ($processors as $rule) {
				list($fields, $class, $params) = array_pad($rule, 3, []);;

				foreach ($fields as $field) {
					if (!is_string($class) || !Reflector::isInstanceOf($class, BaseProcessor::class)) {
						throw new ErrorException("Invalid PostProcessor '{$class}'");
					}

					/**@var BaseProcessor $processor */
					$processor = new $class($field, $params);
					$processor->execute($data);
				}
			}
		}

		$this->responseData = $data;

		return true;
	}

	public function load(IResponse $response, array $requestData) {
		$this->response = $response;
		$this->requestData = [];

		foreach ($requestData as $key => $value) {
			if ($key = 1) {

			}
			//$this->requestData[$key]

		}
		$this->valid = null;
	}
//
//	/**
//	 * @param IResponse $response
//	 * @param $data
//	 *
//	 * @return $this;
//	 */
//	public static function basic(IResponse $response, $data) {
//		$form = new static();
//
//		if ($missing = $form->getMissingFieldsAndValidate($data)) {
//			return $response->sendError(Response::STATUS_CODE_BAD_REQUEST, array_values($missing));
//		}
//
//		if (!$form->isValid()) {
//			return $response->sendError(Response::STATUS_CODE_VALIDATION, $form->getErrors());
//		}
//
//		return $form;
//	}
//
//	#region Processor
//
//	public function load($data) {
//		if (!is_array($data)) {
//			return;
//		}
//
//		$processors = [];
//
//		foreach ($this->postProcessors() as $rule) {
//			list($fields, $class, $params) = array_pad($rule, 3, []);;
//
//			foreach ($fields as $field) {
//				if (!is_string($class) || !Reflector::isInstanceOf($class, BaseProcessor::class)) {
//					throw new ErrorException("Invalid PostProcessor '{$class}'");
//				}
//
//				/**@var BaseProcessor $processor */
//				$processor = new $class($field, $params);
//				$processor->execute($data);
//			}
//		}
//
//		$this->responseData = $data; //TODO
//	}
//
//	/**
//	 * @return bool
//	 */
//	public function emptyResponse() {
//		return empty($this->responseData);
//	}
//
//	/**
//	 * @return array
//	 */
//	public function formatResponse() {
//		return $this->responseData;
//	}
//
//	/**
//	 * @param $name
//	 * @return mixed|null
//	 */
//	public function getResponse($name) {
//		return isset($this->responseData[$name]) ? $this->responseData[$name] : null;
//	}
//
//	/**
//	 * @param $name
//	 * @param $value
//	 */
//	public function setResponse($name, $value) {
//		$this->requestData[$name] = $value;
//	}
//
//	#endregion
//
//	#region Request
//
//	/**
//	 * @param array $data
//	 *
//	 * @return array
//	 * @throws ErrorException
//	 */
//	public function getMissingFieldsAndValidate(array $data) {
//		$this->valid = null;
//		$this->requestData = $data;
//
//		return $this->processRequest();
//	}
//
//	/**
//	 * @return array
//	 * @throws ErrorException
//	 */
//	protected function processRequest() {
//		if (is_null($this->valid)) {
//			$required = [];
//			$validators = [];
//
//			foreach ($this->rules() as $rule) {
//				list($fields, $validator, $params) = array_pad($rule, 3, []);
//
//				if (!is_string($validator) || !Reflector::isInstanceOf($validator, BaseValidator::class)) {
//					throw new ErrorException("Invalid validator '{$validator}'");
//				}
//
//				if (Reflector::isInstanceOf($validator, RequiredValidator::class)) {
//					$required = array_merge($required, $fields);
//				}
//
//				foreach ($fields as $field) {
//					$validators[$field][$validator] = $params;
//				}
//			}
//
//			foreach ($this->requestData as $field => $value) {
//				if (!isset($validators[$field])) {
//					continue;
//				}
//
//				foreach ($validators[$field] as $class => $params) {
//					/**@var BaseValidator $validator */
//					$validator = new $class($field, $params);
//
//					if (!$validator->validate($value)) {
//						$this->errors[$field] = $validator->getMessage();
//						$this->extra[$field] = $validator->getExtra();
//
//						continue;
//					}
//
//					$this->attributes[$field] = $value;
//				}
//			}
//
//			$this->required = array_unique($required);
//			$this->valid = empty($this->errors);
//			$this->afterValidate();
//		}
//
//		return array_diff($this->required, array_keys($this->requestData));
//	}
//
//	protected function afterValidate() {
//		return;
//	}
//
//	public function getAttributes() {
//		return $this->attributes;
//	}
//
//	public function isValid() {
//		return $this->valid;
//	}
//
//	/**
//	 * @param $name
//	 *
//	 * @return mixed|null
//	 */
//	public function getRequest($name) {
//		return isset($this->requestData[$name]) ? $this->requestData[$name] : null;
//	}
//
//	/**
//	 * @return array
//	 */
//	public function requiredFields() {
//		return $this->required;
//	}
//
//	#endregion
//
//	public function getExtra($field) {
//		return !empty($this->extra[$field]) ? $this->extra[$field] : null;
//	}
//
//	public function getErrors() {
//		return $this->errors;
//	}
//
//	public function addError($field, $message) {
//		$this->errors[$field] = $message;
//	}

	abstract protected function rules();

	abstract protected function postProcessors();
}