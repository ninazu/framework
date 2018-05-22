<?php

namespace vendor\ninazu\framework\Form\Processor;

use vendor\ninazu\framework\Form\BaseProcessor;

class UnsetProcessor extends BaseProcessor {

	public function execute(array &$data) {
		unset($data[$this->field]);
	}
}
