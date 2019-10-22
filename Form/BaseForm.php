<?php

namespace vendor\ninazu\framework\Form;

use RuntimeException;
use ReflectionClass;
use vendor\ninazu\framework\Helper\Reflector;

abstract class BaseForm {

	protected $errorFields = [];

	protected $errors = [];

	protected $attributes = [];

	protected $attributeTypes;

	protected $breakOnError = true;

	public function getDeclaredAttributes() {
		$reflect = new ReflectionClass(static::class);
		$phpDoc = $reflect->getDocComment();
		preg_match_all('/@property\s+((\w+)|(\w+)\[])\s+\$(\w+)/', $phpDoc, $matches);
		$this->attributeTypes = array_combine($matches[4], $matches[1]);

		return $this->attributeTypes;
	}

	public function __construct() {
		$this->attributes = array_fill_keys(array_keys($this->getDeclaredAttributes()), null);
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

	public function __isset($name) {
		return array_key_exists($name, $this->attributes);
	}

	public function __unset($name) {
		if (array_key_exists($name, $this->attributes)) {
			$this->attributes[$name] = null;
		}
	}

	public function getAttributes() {
		return $this->attributes;
	}

	public function addError($field, $message, array $extra) {
		$data = [
			'field' => $field,
			'message' => $message,
			'extra' => $extra,
		];

		if (!array_key_exists($field, $this->errorFields)) {
			$this->errorFields[$field] = $data;
		}

		$key = md5(json_encode($data));
		$this->errors[$key] = $data;
	}

	public function validate() {
		$rules = $this->rules();

		foreach ($rules as $rule) {
			list($fields, $class, $params) = array_pad($rule, 3, []);

			if (!is_string($class) || !Reflector::isInstanceOf($class, BaseValidator::class)) {
				throw new RuntimeException("Invalid class of validator '{$class}'");
			}

			foreach ($fields as $field) {
				if ($this->breakOnError && array_key_exists($field, $this->errorFields)) {
					continue;
				}

				/**@var BaseValidator $validator */
				$validator = new $class($field, $params);
				$value = $this->$field;

				if (!$validator->validate($value)) {
					$this->addError($field, $validator->getMessage(), $validator->getExtra());
				}
			}
		}

		return !$this->hasErrors();
	}

	public function getMissingFields() {
		return [];
	}

	public function getErrors() {
		return array_values($this->errors);
	}

	public function getErrorFields() {
		return array_values($this->errorFields);
	}

	public function response(array $data) {
		$processors = $this->processors();

		foreach ($processors as $config) {
			list($fields, $class, $params) = array_pad($config, 3, []);

			if (!is_string($class) || !Reflector::isInstanceOf($class, BaseProcessor::class)) {
				throw new RuntimeException("Invalid class of processor '{$class}'");
			}

			/**@var BaseProcessor $processor */
			$processor = new $class($fields, $params);
			$processor->execute($data);
		}

		return $data;
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

	public function processors() {
		return [];
	}
}