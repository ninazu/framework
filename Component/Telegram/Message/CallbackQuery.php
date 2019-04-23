<?php

namespace vendor\ninazu\framework\Component\Telegram\v2\Message;

use vendor\ninazu\framework\Component\Telegram\v2\User;

/**
 * @property string $gameShortName
 * @property int $id
 * @property User $user
 */
class CallbackQuery extends BaseMessage {

	public function setData($data) {
		$this->id = $data['id'];
		$this->gameShortName = $data['game_short_name'];
		$this->user = new User();
		$this->user->load($data['from']);
	}
}