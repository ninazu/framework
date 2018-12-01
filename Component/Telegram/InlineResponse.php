<?php

namespace vendor\ninazu\framework\Component\Telegram;

class InlineResponse {

	private $data;

	public function add($title, $message, $description = '') {
		$this->data[] = [
			'title' => $title,
			'message' => $message,
			'description' => $description,
		];
	}

	public function asArray() {
		$rows = [];
		$init = 0;

		foreach ($this->data as $row) {
			$init++;
			$tmp = [
				'type' => 'article',
				'id' => (string)$init,
				'title' => $row['title'],
				'input_message_content' => [
					'message_text' => $row['message'],
				],
				'description' => $row['description'],
			];

			$rows[] = $tmp;
		}

		return $rows;
	}
}