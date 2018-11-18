<?php

namespace vendor\ninazu\framework\Form\Validator;

use Exception;

class HashValidator extends StringValidator {

	const HASH_MD5 = 'MD5';

	const HASH_SHA1 = 'SHA1';

	protected $hash = self::HASH_MD5;

	public function init() {
		$hashLen = [
			self::HASH_MD5 => 32,
			self::HASH_SHA1 => 40,
		];

		if (!array_keys($hashLen, $this->hash)) {
			throw new Exception("Hash '{$this->hash}' not implemented");
		}

		$this->min = $hashLen[$this->hash];
		$this->max = $hashLen[$this->hash];
	}

	public function validate($value) {
		if (!$parent = parent::validate($value)) {
			return false;
		}

		if (!preg_match("/^[a-f0-9]{{$this->min}}$/", $value)) {
			$this->message = "Invalid Hash";

			return false;
		}

		return $parent;
	}
}
