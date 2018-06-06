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

	protected $on = [
		BaseModel::ON_WRITE,
		BaseModel::ON_READ,
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
		return [];
	}

	public function hasDependency() {
		return $this->hasDependency;
	}

	abstract public function validate($value);

	abstract public function getMessage();
}