<?php

namespace vendor\ninazu\framework\Form\Processor;

use vendor\ninazu\framework\Form\BaseProcessor;

class FilterProcessor extends BaseProcessor {

	public function execute(array &$data) {
		$data = array_intersect_key($data, array_flip($this->fields));
	}
}