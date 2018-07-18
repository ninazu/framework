<?php

namespace vendor\ninazu\framework\Form;

use vendor\ninazu\framework\Component\Db\Interfaces\IConnection;
use vendor\ninazu\framework\Component\Db\Interfaces\ITransaction;

abstract class BaseForm {

	protected $transaction;

	protected $errors = [];

	protected $attributes = [];

	public function __construct(IConnection $connection) {
		$this->transaction = $connection;
	}

	public function load(array $requestData) {
		//TODO
	}

	public function getResponse() {
		//TODO
		return [];
	}

	public function getMap() {
		return [];
	}

	/**
	 * @return IConnection|ITransaction
	 */
	public function getTransaction() {
		return $this->transaction;
	}

	public function __get($name) {
		if (isset($this->attributes[$name])) {
			return $this->attributes[$name];
		}

		return null;
	}

	public function __set($name, $value) {
		$this->attributes[$name] = $value;
	}

	public function addError($field, $message) {
		$data = [
			'field' => $field,
			'message' => $message,
		];
		$key = md5(json_encode($data));
		$this->errors[$key] = $data;
	}
}