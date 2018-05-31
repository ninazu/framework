<?php

namespace vendor\ninazu\framework\Helper;

use ErrorException;
use ReflectionClass;
use ReflectionException;

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
	 * @throws ErrorException
	 * @throws ReflectionException
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

			if (empty($results)) {
				throw new ErrorException('Constant group not found');
			}

			self::$constantGroup[$className][$needle] = $results;
		}

		return new self(self::$constantGroup[$className][$needle]);
	}

	public static function isInstanceOf($className, $instanceName) {
		if ($className === $instanceName) {
			return true;
		}

		$instance = new ReflectionClass($instanceName);
		$class = new ReflectionClass($className);

		if ($instance->isInterface()) {
			return $class->implementsInterface($instance);
		} else {
			return $class->isSubclassOf($instance);
		}
	}

	public static function getClassShortName($className) {
		$class = new ReflectionClass($className);

		return $class->getShortName();
	}

	public static function isAssocArray(array $array) {
		if (array() === $array) {
			return false;
		}

		return array_keys($array) !== range(0, count($array) - 1);
	}

	public static function toFlatArray(array $array, $namespace = null) {
		$tmp = [];

		foreach ($array as $key => $value) {
			$flatKey = !is_null($namespace) ? "{$namespace}.{$key}" : $key;

			if (is_array($value)) {
				$tmp = array_merge($tmp, self::toFlatArray($value, $flatKey));
			} else {
				$tmp[$flatKey] = $value;
			}
		}

		return $tmp;
	}

	public static function flatKeyToLink(array $tree, $key) {
		$keys = explode('.', $key);

		foreach ($keys as $key) {
			if (!isset($tree[$key])) {
				return null;
			}

			$tree = &$tree[$key];
		}

		return [
			$key => $tree,
		];
	}
}