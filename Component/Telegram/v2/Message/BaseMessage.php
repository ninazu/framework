<?php

namespace vendor\ninazu\framework\Component\Telegram\v2\Message;

abstract class BaseMessage {

	protected $messageId;

	public function __construct($data) {
		//$this->messageId = @$data['message_id'];
	}
}