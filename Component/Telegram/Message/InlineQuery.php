<?php

namespace vendor\ninazu\framework\component\Telegram\Message;

class InlineQuery extends BaseMessage {

	function mainKey(): string {
		return "inline_query";
	}
}