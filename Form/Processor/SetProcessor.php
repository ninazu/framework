<?php

namespace vendor\ninazu\framework\Form\Processor;

use Closure;
use RuntimeException;
use vendor\ninazu\framework\Form\BaseProcessor;

class SetProcessor extends BaseProcessor {

	/**
	 * @var Closure $callback
	 */
	protected $callback;

	protected $value;

	public function init() {
		if (isset($this->callback)) {
			if (!is_callable($this->callback)) {
				throw new RuntimeException('Callback must be callable');
			}

			//$f = new ReflectionFunction($this->callback);
			//$params = $f->getParameters();

			$this->callback->bindTo($this);
		}
	}

	public function execute(array &$data) {
		if (isset($this->callback)) {
			$callback = $this->callback;
			$callback($data, $this->fields);
		} else {
			foreach ($this->fields as $field) {
				$data[$field] = $this->value;
			}
		}
	}
}