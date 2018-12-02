<?php

namespace vendor\ninazu\framework\component\Telegram\Message;

class EditMessage extends BaseMessage {

	function mainKey(): string {
		return "edited_message";
	}
}