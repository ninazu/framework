<?php

namespace vendor\ninazu\framework\Form\Validator;

use vendor\ninazu\framework\Form\BaseValidator;

class InRangeValidator extends BaseValidator {

	protected $range;

	protected $min;

	protected $max;

	public function validate($value) {
		if ($this->range) {
			return array_key_exists($value, $this->range);
		} else {
			return (empty($this->min) || $value >= $this->min) && (empty($this->max) || $value <= $this->max);
		}
	}

	public function getMessage() {
		if ($this->range) {
			$range = implode(', ', $this->range);

			return "Field '{$this->field}' not in range ($range)";
		} else {
			if (empty($this->min) && !empty($this->max)) {
				return "Field '{$this->field}' more than {$this->max}";
			} elseif (empty($this->max) && !empty($this->min)) {
				return "Field '{$this->field}' less than {$this->min}";
			} else {
				return "Field '{$this->field}' must be between {$this->min} and {$this->max}";
			}
		}
	}

}