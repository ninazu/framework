<?php

namespace vendor\ninazu\framework\Component\Telegram\v2;

/**
 * @property string $type
 * @property int $offset
 * @property int $length
 * @property string $url*
 * @property User $user*
 * @property string $text
 */
class MessageEntity extends BaseReader {

	const TYPE_HASH_TAG = "hashtag";

	const TYPE_CASH_TAG = "cashtag";

	const TYPE_BOT_COMMAND = "bot_command";

	const TYPE_URL = "url";

	const TYPE_EMAIL = "email";

	const TYPE_PHONE_NUMBER = "phone_number";

	const TYPE_BOLD = "bold";

	const TYPE_ITALIC = "italic";

	const TYPE_CODE = "code";

	const TYPE_TEXT_LINK = "text_link";

	const TYPE_TEXT_MENTION = "text_mention";

	const TYPE_MENTION = "mention";
}