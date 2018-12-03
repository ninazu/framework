<?php

namespace vendor\ninazu\framework\Component\Telegram\v2;

use TypeError;
use vendor\ninazu\framework\Component\Telegram\v2\Message\DummyMessage;

class Request {

	private $message;

	public function __construct($data) {
		if (!$data = json_decode($data, true)) {
			$this->message = new DummyMessage();

			return;
		}

		$map = [
			'message' => 'Message',
			'edited_message' => 'Message',
			'channel_post' => 'Message',
			'edited_channel_post' => 'Message',
			'inline_query' => 'InlineQuery',
			'chosen_inline_result' => 'ChosenInlineResult',
			'callback_query' => 'CallbackQuery',
			'shipping_query' => 'ShippingQuery',
			'pre_checkout_query' => 'PreCheckoutQuery',
		];

		foreach ($map as $key => $class) {
			if (isset($data[$key])) {
				$className = __NAMESPACE__ . "\Message\\{$class}";
				$this->message = new $className($data[$key]);
				break;
			}
		}

		if (!$this->message) {
			throw new TypeError('Undefined message type');
		}
	}

	public function getMessage() {
		return $this->message;
	}
}