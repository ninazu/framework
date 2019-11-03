<?php

namespace vendor\ninazu\framework\Component;

use vendor\ninazu\framework\Core\BaseComponent;

class Translate extends BaseComponent {

	protected $lang;

	public function getLang() {
		return $this->lang;
	}

	public function setLang($lang) {
		$this->lang = $lang;
	}

	public function translate(string $text) {
		return $text;
	}
}