<?php

namespace vendor\ninazu\framework\Component\Telegram\v2\Message;

use vendor\ninazu\framework\Component\Telegram\v2\User;

/**
 * @property User $user
 */
class DummyMessage extends BaseMessage {

	public function setData($data) {
		$this->user = new User();
		$this->user->load([
			"id" => 212856439,
			"is_bot" => false,
			"first_name" => "Co.",
			"last_name" => "In",
			"username" => "co_in",
			"language_code" => "ru",
		]);
	}
}