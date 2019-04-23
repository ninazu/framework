<?php

namespace vendor\ninazu\framework\Core;

use ReflectionClass;
use ReflectionProperty;

abstract class BaseConfigurator {

	protected $config;

	/**
	 * @param array $config
	 */
	protected function fillFromConfig($config) {
		$this->config = $config;
		$reflect = new ReflectionClass($this);
		$props = $reflect->getProperties(ReflectionProperty::IS_PROTECTED);

		foreach ($props as $prop) {
			$propertyName = $prop->getName();

			if (isset($config[$propertyName])) {
				if (is_array($config[$propertyName])) {
					$this->$propertyName = array_replace_recursive(
						is_array($this->$propertyName) ? $this->$propertyName : [], $config[$propertyName]
					);
				} else {
					$this->$propertyName = $config[$propertyName];
				}
			}
		}
	}
}