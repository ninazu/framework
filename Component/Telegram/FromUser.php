<?php

namespace vendor\ninazu\framework\Component\Telegram;

/**
 * @property int $userId
 * @property int $firstName
 * @property int $lastName
 * @property int $username
 * @property int $isBot
 * @property int $language
 */
class FromUser extends BaseReader {

	/**
	 * @var Bot $bot
	 */
	private $bot;

	public function setBot(Bot $bot) {
		$this->bot = $bot;
	}

	public function sendMessage($message) {
		$this->bot->sendMessage(
			$this->userId,
			$message
		);
	}
}