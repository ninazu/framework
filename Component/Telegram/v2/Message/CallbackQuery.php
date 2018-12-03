<?php

namespace vendor\ninazu\framework\Component\Telegram\v2\Message;

/**
 * @property string gameShortName
 * @property int $id
 */
class CallbackQuery extends BaseMessage {

	public function setData($data) {
		$this->id = $data['id'];
		$this->gameShortName = $data['game_short_name'];
	}
}