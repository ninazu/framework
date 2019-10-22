<?php

namespace vendor\ninazu\framework\Form\Validator;

use Exception;
use http\Exception\RuntimeException;

class HashValidator extends StringValidator {

	const HASH_MD5 = 'MD5';

	const HASH_SHA1 = 'SHA1';

	protected $hash = self::HASH_MD5;

	public function init() {
		$hashLength = [
			self::HASH_MD5 => 32,
			self::HASH_SHA1 => 40,
		];

		if (!array_key_exists($this->hash, $hashLength)) {
			throw new RuntimeException("Hash '{$this->hash}' not implemented");
		}

		$this->min = $hashLength[$this->hash];
		$this->max = $hashLength[$this->hash];
	}

	public function validate($value, &$newValue) {
		if (!$parent = parent::validate($value, $newValue)) {
			return false;
		}

		if (!preg_match("/^[a-f0-9]{{$this->min}}$/", $value)) {
			$this->message = "Invalid Hash";

			return false;
		}

		return $parent;
	}
}
