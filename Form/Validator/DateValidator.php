<?php

namespace vendor\ninazu\framework\Form\Validator;

use RuntimeException;
use vendor\ninazu\framework\Form\BaseValidator;
use vendor\ninazu\framework\Helper\Reflector;

class DateValidator extends BaseValidator {

	const FORMAT_DATETIME = 'Y-m-d H:i:s';

	const FORMAT_DATE = 'Y-m-d';

	protected $min;

	protected $max;

	protected $format;

	public function init() {
		if (isset($this->format) && !array_key_exists($this->format, Reflector::getConstantGroup(self::class, "FORMAT_")->getData())) {
			throw new RuntimeException('Invalid date format');
		}

		if ($this->min) {
			$dateTime = date_create_from_format(self::FORMAT_DATETIME, $this->min);

			if (!$dateTime) {
				throw new RuntimeException('Invalid min format');
			}

			$this->min = $dateTime->format($this->format);
		}

		if ($this->min) {
			$dateTime = date_create_from_format(self::FORMAT_DATETIME, $this->max);

			if (!$dateTime) {
				throw new RuntimeException('Invalid max format');
			}

			$this->max = $dateTime->format($this->format);
		}

		return parent::init();
	}

	public function validate($value, &$newValue) {
		if (!preg_match('/^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])(?:( [0-2][0-9]):([0-5][0-9]):([0-5][0-9]))?$/', $value)) {
			return false;
		}

		if (!$dateTime = date_create_from_format($this->format, $value)) {
			return false;
		}

		$newValue = $dateTime->format($this->format);
		$value = $newValue;

		if ($this->min && $this->min > $value) {
			if (!$this->message) {
				$this->message = "Field '{$this->field}' less than '{$this->min}'";
			}

			return false;
		}

		if ($this->max && $this->max < $value) {
			if (!$this->message) {
				$this->message = "Field '{$this->field}' more than '{$this->max}'";
			}

			return false;
		}

		return true;
	}

	public function getMessage() {
		if ($this->message) {
			return $this->message;
		}

		$map = [
			self::FORMAT_DATE => 'date',
			self::FORMAT_DATETIME => 'dateTime',
		];

		return "Field '{$this->field}' is not a valid {$map[$this->format]} format expected '{$this->format}'";
	}

	public static function getRealDate($ageLimit = null) {
		return [
			'min' => date(self::FORMAT_DATETIME, strtotime("-100 years", time())),
			'max' => date(self::FORMAT_DATETIME, $ageLimit ? strtotime("-{$ageLimit} years", time()) : time()),
		];
	}
}