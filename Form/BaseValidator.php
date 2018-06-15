<?php

namespace vendor\ninazu\framework\Form;

use vendor\ninazu\framework\Component\Response\Response;
use vendor\ninazu\framework\Core\BaseConfigurator;

abstract class BaseValidator extends BaseConfigurator {

	protected $field;

	/**@var Response $response */
	protected $response;

	protected $hasDependency = false;

	protected $message;

	protected $extra = [];

	protected $on = [
		BaseModel::ON_WRITE => true,
		BaseModel::ON_READ => true,
	];

	public function __construct($field, $params, $response) {
		$this->field = $field;
		$this->response = $response;
		$this->fillFromConfig($params);
		$this->init();
	}

	protected function init() {
		return;
	}

	public function getExtra() {
		return $this->extra;
	}

	public function setExtra($extra) {
		$this->extra = $extra;
	}

	public function getScenarios() {
		return $this->on;
	}

	public function hasDependency() {
		return $this->hasDependency;
	}

	public function setMessage($message) {
		$this->message = $message;
	}

	public function setField($field) {
		$this->field = $field;
	}

	public function getField() {
		return $this->field;
	}

	abstract public function validate($value);

	abstract public function getMessage();
}