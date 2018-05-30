<?php

namespace vendor\ninazu\framework\Form;

use vendor\ninazu\framework\Component\Response\Response;
use vendor\ninazu\framework\Core\BaseConfigurator;

abstract class BaseValidator extends BaseConfigurator {

	protected $field;

	protected $index;

	/**@var Response $response */
	protected $response;

	protected $hasDependency = false;

	public function __construct($field, $params, $response, $index = null) {
		$this->field = $field;
		$this->index = $index;
		$this->response = $response;
		$this->fillFromConfig($params);
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