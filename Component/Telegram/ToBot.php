<?php

namespace vendor\ninazu\framework\Component\Telegram;

/**
 * @property int $messageId
 * @property bool $isPrivateChat
 * @property bool $isCallback       This is message or buttonQuery
 * @property bool $isInline         This is message or buttonQuery
 * @property bool $isInvite         This is message or buttonQuery
 * @property int $forwardFrom       This is message or buttonQuery
 * @property string $command        Command string
 */
class ToBot extends BaseReader {

}