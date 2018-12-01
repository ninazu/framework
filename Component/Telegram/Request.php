<?php

namespace vendor\ninazu\framework\Component\Telegram;

/**
 * @property FromUser $fromUser   User who send message or click callback button
 * @property ToBot $toBot      Chat with Bot
 *
 * @property array $content
 */
class Request extends BaseReader {

	public function __construct() {
		parent::__construct();

		$content = json_decode(file_get_contents("php://input"), true);
		$to = new ToBot();

		if (!empty($content['message']['forward_from']) && empty($content['message']['forward_from']['is_bot'])) {
			$to->forwardFrom = $content['message']['forward_from']['id'];
		}

		if (!empty($content['inline_query'])) {
			$to->isCallback = false;
			$to->isInline = true;
			$to->command = $content['inline_query']['query'];
			$to->isPrivateChat = false;
			$to->messageId = null;
			$fromData = $content['inline_query']['from'];
		} else {
			$to->isCallback = isset($content['callback_query']);
			$to->isInline = false;
			$to->command = $to->isCallback ? $content['callback_query']['data'] : $content['message']['text'];
			$to->isPrivateChat = ($to->isCallback ? $content['callback_query']['message']['chat']['type'] : $content['message']['chat']['type']) === 'private';
			$to->messageId = $to->isCallback ? $content['callback_query']['message']['message_id'] : null;
			$fromData = $to->isCallback ? $content['callback_query']['from'] : $content['message']['from'];
		}

		$from = new FromUser();
		$from->userId = $fromData['id'];
		$from->firstName = $fromData['first_name'];
		$from->isBot = (!empty($fromData['is_bot'])) ? true : false;

		if (isset($fromData['last_name'])) {
			$from->lastName = $fromData['last_name'];
		}

		if (isset($fromData['username'])) {
			$from->username = $fromData['username'];
		}

		if (isset($fromData['language_code'])) {
			$from->language = $fromData['language_code'];
		}

		$this->content = $content;
		$this->fromUser = $from;
		$this->toBot = $to;
	}
}