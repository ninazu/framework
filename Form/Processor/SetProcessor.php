<?php

namespace vendor\ninazu\framework\Form\Processor;

use ErrorException;
use vendor\ninazu\framework\Form\BaseProcessor;

class SetProcessor extends BaseProcessor {

	protected $callback;

	protected $value;

	public function execute(array &$data) {
		if (isset($this->callback)) {
			$callback = $this->callback;
			$callback($data, $this->field);
		} elseif (isset($this->value)) {
			$data[$this->field] = $this->value;
		} else {
			throw new ErrorException('SetProcessor without callback or value in params');
		}
	}
}