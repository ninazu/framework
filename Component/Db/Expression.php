<?php

namespace vendor\ninazu\framework\Component\Db;

use RuntimeException;
use vendor\ninazu\framework\Helper\Formatter;

class Expression {

	protected $expression;

	public function __construct($expression) {
		if (!is_string($expression)) {
			throw new RuntimeException('SQL Expression must be a string');
		}

		$this->expression = $expression;
	}

	public function __toString() {
		return Formatter::removeLeftTabs($this->expression);
	}
}