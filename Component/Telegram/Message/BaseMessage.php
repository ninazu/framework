<?php

namespace vendor\ninazu\framework\Component\Telegram\v2\Message;

use vendor\ninazu\framework\Component\Telegram\v2\BaseReader;

abstract class BaseMessage extends BaseReader {

	abstract public function setData($data);
}