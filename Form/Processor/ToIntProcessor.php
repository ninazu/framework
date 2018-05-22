<?php

namespace vendor\ninazu\framework\Form\Processor;

use vendor\ninazu\framework\Form\BaseProcessor;

class ToIntProcessor extends BaseProcessor {

	public function execute(array &$data) {
		$data[$this->field] = (int)$data[$this->field];
	}
}