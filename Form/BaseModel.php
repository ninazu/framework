<?php

namespace vendor\ninazu\framework\Form;

use vendor\ninazu\framework\Component\Response\Response;

abstract class BaseModel extends BaseForm {

	public function refresh() {
		$this->attributes = $this->find();
	}

	public function oldAttributes() {
		if (!$this->oldAttributes) {
			$this->oldAttributes = $this->find();
		}

		return $this->oldAttributes;
	}

	public function beforeSave() {
		if (!$this->validate()) {
			if ($this->hasErrors()) {
				$this->response->sendError(Response::STATUS_CODE_VALIDATION, array_values($this->errors));
			}
		}
	}

	abstract function save();

	abstract function find();
}