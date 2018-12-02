<?php

namespace vendor\ninazu\framework\component\Telegram\Message;

class CallbackQuery extends BaseMessage {

	function mainKey(): string {
		return 'callback_query';
	}
}