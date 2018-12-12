<?php

namespace vendor\ninazu\framework\Component\Telegram\v2\Message;

use vendor\ninazu\framework\Component\Telegram\v2\User;

/**
 * @property User $user
 */
class Message extends BaseMessage {

	public function setData($data) {
		$this->user = new User();
		$this->user->load($data['from']);
	}
}