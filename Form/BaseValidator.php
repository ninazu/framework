<?php

namespace vendor\ninazu\framework\Form;

use vendor\ninazu\framework\Component\Response\Response;
use vendor\ninazu\framework\Core\BaseConfigurator;

abstract class BaseValidator extends BaseConfigurator {

	protected $field;

	protected $index;

	/**@var Response $response */
	protected $response;

	public function __construct($field, $params, $response, $index = null) {
		$this->field = $field;
		$this->index = $index;
		$this->fillFromConfig($params);
		$this->init();
	}

	public function init() {
		return;
	}

	public function getExtra() {
		return [];
	}

	abstract public function validate($value);

	abstract public function getMessage();
}