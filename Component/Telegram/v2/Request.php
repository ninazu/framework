<?php

namespace vendor\ninazu\framework\Component\Telegram\v2;

use TypeError;
use vendor\ninazu\framework\Component\Telegram\v2\Message\BaseMessage;
use vendor\ninazu\framework\Component\Telegram\v2\Message\DummyMessage;

class Request {

	/**@var BaseMessage $message */
	private $message;

	private $rawData;

	public function __construct($data) {
		$this->rawData = $data;

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
				$this->message = new $className();
				$this->message->setData($data[$key]);

				break;
			}
		}

		if (!$this->message) {
			throw new TypeError('Undefined message type');
		}
	}

	public function getRawData(): string {
		return $this->rawData;
	}

	public function getMessage(): array {
		return $this->message;
	}
}