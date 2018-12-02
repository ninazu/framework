<?php

use vendor\ninazu\framework\component\Telegram\v2\Message\CallbackQuery;
use vendor\ninazu\framework\component\Telegram\v2\Message\ChosenInlineResult;
use vendor\ninazu\framework\component\Telegram\v2\Message\InlineQuery;
use vendor\ninazu\framework\component\Telegram\v2\Message\Message;
use vendor\ninazu\framework\component\Telegram\v2\Message\PreCheckoutQuery;
use vendor\ninazu\framework\component\Telegram\v2\Message\ShippingQuery;

class Request {

	public function __construct($data) {
		$map = [
			'message' => Message::class,
			'edited_message' => Message::class,
			'channel_post' => Message::class,
			'edited_channel_post' => Message::class,
			'inline_query' => InlineQuery::class,
			'chosen_inline_result' => ChosenInlineResult::class,
			'callback_query' => CallbackQuery::class,
			'shipping_query' => ShippingQuery::class,
			'pre_checkout_query' => PreCheckoutQuery::class,
		];
	}
}