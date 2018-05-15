<?php

namespace vendor\ninazu\framework\Helper;

use ReflectionClass;

class Reflector {

	private $data;

	private static $constantGroup = [];

	private function __construct($data) {
		$this->data = $data;
	}

	/**
	 * array_fill_keys
	 *
	 * ['SOME_VALUE'=>null]
	 *
	 * @param $value
	 *
	 * @return $this
	 */
	public function fillKeys($value) {
		$this->data = array_fill_keys($this->data, $value);

		return $this;
	}

	/**
	 * array_flip
	 *
	 * ['SOME_VALUE'=>'SOME_KEY']
	 *
	 * @return $this
	 */
	public function valuesAsKey() {
		$this->data = array_flip($this->data);

		return $this;
	}

	/**
	 * SOME_VALUE => some_value
	 *
	 * @return $this
	 */
	public function convertValuesToLower() {
		$this->data = array_map(function ($value) {
			return strtolower($value);
		}, $this->data);

		return $this;
	}

	/**
	 * SOME_VALUE => SomeValue
	 *
	 * @return $this
	 */
	public function convertValuesToCamelCase() {
		$this->data = array_map(function ($value) {
			return str_replace(' ', '', ucwords(str_replace('_', ' ', strtolower($value))));
		}, $this->data);

		return $this;
	}

	/**
	 * SOME_VALUE => Some value
	 *
	 * @return $this
	 */
	public function convertValuesToTitle() {
		$this->data = array_map(function ($value) {
			return ucfirst(str_replace('_', ' ', strtolower($value)));
		}, $this->data);

		return $this;
	}

	/**
	 * Return result of manipulations
	 *
	 * @return mixed
	 */
	public function getData() {
		return $this->data;
	}

	/**
	 * @param string $className
	 * @param string $needle
	 *
	 * @return $this
	 *
	 * @throws \ReflectionException
	 */
	public static function getConstantGroup($className, $needle) {
		if (!isset(self::$constantGroup[$className][$needle])) {
			$class = new ReflectionClass($className);
			$constants = $class->getConstants();
			$results = [];
			$needleLen = strlen($needle);

			foreach ($constants as $constant => $value) {
				if (strpos($constant, $needle) === 0) {
					$results[$value] = substr($constant, $needleLen);
				}
			}

			self::$constantGroup[$className][$needle] = $results;
		}

		return new self(self::$constantGroup[$className][$needle]);
	}

	public static function isInstanceOf($className, $instanceName) {
		$instance = new ReflectionClass($instanceName);
		$class = new ReflectionClass($className);

		if ($instance->isInterface()) {
			return $class->implementsInterface($instance);
		} else {
			return $class->isSubclassOf($instance);
		}
	}
}