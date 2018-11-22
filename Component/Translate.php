<?php

namespace vendor\ninazu\framework\Component;

use vendor\ninazu\framework\Core\BaseComponent;

class Translate extends BaseComponent {

	protected $defaultLang = 'en';

	protected $lang;

	public function getLang() {
		return $this->lang;
	}

	public function setLang($lang) {
		//TODO Sanitize lang
		$this->lang = $lang;
	}
}