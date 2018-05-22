<?php

namespace vendor\ninazu\framework\Form\Processor;

use vendor\ninazu\framework\Form\BaseProcessor;

class ToBoolProcessor extends BaseProcessor {

	public function execute(array &$data, $field) {
		$data[$field] = (bool)(int)$data[$field];
	}
}