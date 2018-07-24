<?php

namespace vendor\ninazu\framework\Form;

use ErrorException;
use ReflectionClass;
use vendor\ninazu\framework\Component\Db\Interfaces\IConnection;
use vendor\ninazu\framework\Component\Db\Interfaces\ITransaction;
use vendor\ninazu\framework\Component\Response\IResponse;
use vendor\ninazu\framework\Helper\Reflector;

abstract class BaseForm {

	/**@var IConnection|ITransaction $transaction */
	protected $transaction;

	/**@var IResponse */
	protected $response;

	protected $errors = [];

	protected $attributes = [];

	protected $oldAttributes = [];

	public function __construct() {
		$reflect = new ReflectionClass(static::class);
		$phpDoc = $reflect->getDocComment();
		preg_match_all('/\@property\s+\w+\s+\$(\w+)/', $phpDoc, $matches);
		$this->attributes = array_fill_keys($matches[1], null);
	}

	public static function createWithResponse(IResponse $response, ITransaction $connection) {
		$instance = new static();
		$instance->transaction = $connection;
		$instance->response = $response;

		return $instance;
	}

	public function load(array $requestData) {
		$attributeKeys = array_keys($this->attributes);

		foreach ($attributeKeys as $key) {
			if (array_key_exists($key, $requestData)) {
				$this->attributes[$key] = $requestData[$key];
			}
		}

		return $this;
	}

	public function reset() {
		array_fill_keys(array_keys($this->attributes), null);
	}

	public function getResult() {
		//TODO
		return [];
	}

	/**
	 * @return IConnection|ITransaction
	 */
	public function getTransaction() {
		return $this->transaction;
	}

	public function __get($name) {
		if (array_key_exists($name, $this->attributes)) {
			return $this->attributes[$name];
		}

		return null;
	}

	public function __set($name, $value) {
		if (array_key_exists($name, $this->attributes)) {
			$this->attributes[$name] = $value;
		}
	}

	public function getAttributes() {
		return $this->attributes;
	}

	public function addError($field, $message) {
		$data = [
			'field' => $field,
			'message' => $message,
		];
		$key = md5(json_encode($data));
		$this->errors[$key] = $data;
	}

	public function validate() {
		$rules = $this->rules();

		foreach ($rules as $rule) {
			list($fields, $class, $params) = array_pad($rule, 3, []);

			if (!is_string($class) || !Reflector::isInstanceOf($class, BaseValidator::class)) {
				throw new ErrorException("Invalid class of validator '{$class}'");
			}

			foreach ($fields as $field) {
				/**@var BaseValidator $validator */
				$validator = new $class($field, $params, $this->response);

				if (!$validator->validate($this->$field)) {
					$this->addError($field, $validator->getMessage());
				}
			}
		}

		return !$this->hasErrors();
	}

	/**
	 * @return bool
	 */
	protected function hasErrors() {
		return !empty($this->errors);
	}

	public function rules() {
		return [];
	}
}