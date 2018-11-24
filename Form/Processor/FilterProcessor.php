<?php

namespace vendor\ninazu\framework\Form\Processor;

use vendor\ninazu\framework\Form\BaseProcessor;

class FilterProcessor extends BaseProcessor {

	protected $unset = false;

	public function execute(array &$data) {
		if ($this->unset) {
			foreach ($this->fields as $field) {
				unset($data[$field]);
			}
		} else {
			$data = array_intersect_key($data, array_flip($this->fields));
		}
	}
}