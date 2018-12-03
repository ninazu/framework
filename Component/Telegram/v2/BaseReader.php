<?php

namespace vendor\ninazu\framework\Component\Telegram\v2;

use Exception;
use ReflectionClass;

abstract class BaseReader {

	protected $attributes = [];

	public function __construct() {
		$reflect = new ReflectionClass(static::class);
		$phpDoc = $reflect->getDocComment();
		preg_match_all('/\@property\s+((\w+)|(\w+)\[\])\s+\$(\w+)/', $phpDoc, $matches);

		$this->attributes = array_fill_keys($matches[4], null);
	}

	public function getAttributes() {
		return $this->attributes;
	}

	public function __get($name) {
		if (!array_key_exists($name, $this->attributes)) {
			$class = static::class;
			throw new Exception("Get attribute undefined '{$class}::{$name}'");
		}

		return $this->attributes[$name];
	}

	public function __set($name, $value) {
		if (!array_key_exists($name, $this->attributes)) {
			$class = static::class;
			throw new Exception("Set attribute undefined '{$class}::{$name}'");
		}

		$this->attributes[$name] = $value;
	}
}