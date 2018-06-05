<?php

namespace vendor\ninazu\framework\Form;

use vendor\ninazu\framework\Core\BaseConfigurator;

abstract class BaseProcessor extends BaseConfigurator {

	protected $fields;

	public function __construct($fields, $params) {
		$this->fields = $fields;
		$this->fillFromConfig($params);
		$this->init();
	}

	public function init() {
		return;
	}

	abstract public function execute(array &$data);
}