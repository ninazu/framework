<?php

namespace vendor\ninazu\framework\Form;

use vendor\ninazu\framework\Component\Db\Interfaces\IConnection;
use vendor\ninazu\framework\Component\Db\Interfaces\ITransaction;
use vendor\ninazu\framework\Component\Response\IResponse;

abstract class BaseForm {

	/**@var IConnection $transaction */
	protected $transaction;

	protected $response;

	protected $errors = [];

	protected $attributes = [];

	public function __construct(IResponse $response, ITransaction $connection) {
		$this->transaction = $connection;
		$this->response = $response;
	}

	public function load(array $requestData) {
		//TODO
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
		if (isset($this->attributes[$name])) {
			return $this->attributes[$name];
		}

		return null;
	}

	public function __set($name, $value) {
		$this->attributes[$name] = $value;
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
}