<?php

namespace vendor\ninazu\framework\Component\Telegram\v2\Message;

use vendor\ninazu\framework\Component\Telegram\v2\Chat;
use vendor\ninazu\framework\Component\Telegram\v2\MessageEntity;
use vendor\ninazu\framework\Component\Telegram\v2\User;

/**
 * @property User $from
 * @property Chat $chat
 * @property Message $reply reply_to_message
 * @property string $text*
 * @property MessageEntity[] $entities*
 * @property MessageEntity[] $captionEntities* caption_entities
 */
class Message extends BaseMessage {

	public function setData($data) {
		$this->from = (new User())
			->load($data['from']);

		$this->chat = (new Chat())
			->load($data['chat']);

		if (isset($data['reply_to_message'])) {
			$this->reply = (new Message())
				->setData($data['reply_to_message']);
		}

		if (isset($data['text'])) {
			$this->text = $data['text'];
		}

		if (isset($data['entities'])) {
			foreach ($data['entities'] as $entity) {
				$this->entities[] = (new MessageEntity())
					->load($entity);
			}
		}

		return $this;
	}

	public function getEntities() {
		$list = [];

		if (!empty($this->attributes['entities'])) {
			foreach ($this->entities as $index => $entity) {
				$list[$index] = substr($this->text, $entity->offset, $entity->length);
			}
		}

		return $list;
	}
}