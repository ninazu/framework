<?php

namespace vendor\ninazu\framework\Component\Telegram\v2;

/**
 * @property int $id
 * @property bool $isBot            is_bot
 * @property string $firstName      first_name
 * @property string $lastName*      last_name
 * @property string $username*
 * @property string $languageCode*  language_code
 */
class User extends BaseReader {

	public function getSafeName() {
		$name = trim("{$this->firstName} {$this->lastName}");

		if (empty($name)) {
			$name = $this->username;
		}

		if (empty($name)) {
			$name = $this->id;
		}

		return $name;
	}
}