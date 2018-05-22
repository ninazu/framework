<?php

namespace vendor\ninazu\framework\Form;

use vendor\ninazu\framework\Core\BaseConfigurator;

abstract class BaseProcessor extends BaseConfigurator {

	protected $field;

	public function __construct($field, $params) {
		$this->field = $field;
		$this->fillFromConfig($params);
		$this->init();
	}

	public function init() {
		return;
	}

	abstract public function execute(array &$data);
}