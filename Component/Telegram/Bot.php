<?php

namespace vendor\ninazu\framework\Component\Telegram;

use Exception;
use vendor\ninazu\framework\Core\BaseComponent;

/**
 * @property-read Request $request
 * @property-read Response $response
 * */
class Bot extends BaseComponent {

	private $predefinedMarkUp;

	private $paginationMarkUp;

	protected $key;

	protected $secureParam;

	protected $webHookUrl;

	protected $botName;

	public $request;

	public $response;

	const TYPE_INLINE = 'inline_keyboard';

	const TYPE_REPLY = 'inline_keyboard';

	public function init() {
		$this->response = new Response();
		$this->request = new Request();
	}

	public function setPredefinedButton($buttons) {
		$this->predefinedMarkUp = $buttons;

		return $this;
	}

	public function getBotName() {
		return $this->botName;
	}

	public function getSecureParam() {
		return $this->secureParam;
	}

	public function install() {
		return $this->request('setWebhook', [
			'url' => $this->webHookUrl,
		]);
	}

	public function sendMessage($chatID, $message) {
		return $this->request('sendMessage', [
			'chat_id' => $chatID,
			'text' => $message,
			'parse_mode' => 'HTML',
		]);
	}

	public function markUp($chatID, $message, $buttons, $inline = false) {
		$params = [
			'chat_id' => $chatID,
			'text' => $message,
			'parse_mode' => 'HTML',
			'reply_markup' => [
				'inline_keyboard' => $this->prepareQueryButtons($buttons, $inline),
			],
		];

		return $this->request('sendMessage', $params);
	}

	public function deleteMessage($chatId, $messageID) {
		$response = $this->request('deleteMessage', [
			'chat_id' => $chatId,
			'message_id' => $messageID,
		]);

		return $response;
	}

	public function replyKeyboardMarkup($chatId, $messageID, $text, array $buttons) {
		if (!empty($messageID)) {
			$this->deleteMessage($chatId, $messageID);
		}

		$response = $this->request('sendMessage', [
				'chat_id' => $chatId,
				'text' => $text,
				'parse_mode' => 'HTML',
				'reply_markup' => [
					'one_time_keyboard' => true,
					'resize_keyboard' => true,
					'keyboard' => $this->prepareButtons($buttons),
				],
			]
		);

		return $response;
	}

	public function hideKeyboard($chatId, $text) {
		$response = $this->request('sendMessage', [
				'chat_id' => $chatId,
				'text' => $text,
				'parse_mode' => 'HTML',
				'reply_markup' => [
					'remove_keyboard' => true,
				],
			]
		);

		return $response;
	}

	public function updateMarkUp($chatID, $messageID, $text, $buttons, $inline = false) {
		if (empty($messageID)) {
			$response = $this->markUp($chatID, $text, $buttons, $inline);

			return $response;
		}

		$response = $this->request('editMessageText', [
				'chat_id' => $chatID,
				'message_id' => $messageID,
				'parse_mode' => 'HTML',
				'text' => $text,
				'reply_markup' => [
					'inline_keyboard' => $this->prepareQueryButtons($buttons, $inline),
				],
			]
		);

		if (!$response['status']) {
			$response = $this->markUp($chatID, $text, $buttons, $inline);
		}

		return $response;
	}

	public static function getResponse() {
		$content = file_get_contents("php://input");

		if (!$response = json_decode($content, true)) {
			return null;
		}

		return $response;
	}

	private function prepareButtons($buttons) {
		$result = [];

		foreach ($buttons as $text) {
			$result[] = [
				['text' => $text],
			];
		}

		return $result;
	}

	private function prepareQueryButtons($buttons, $inline) {
		$inlineKeyboard = [];

		if (!$inline) {
			foreach ($buttons as $buttonText => $buttonData) {
				if (preg_match('/^(https?\:\/\/|tg\:\/\/)/', $buttonData)) {
					$inlineKeyboard[][] = [
						'text' => $buttonText,
						'url' => $buttonData,
						'callback_data' => (string)$buttonData,
					];
				} else {
					$inlineKeyboard[][] = [
						'text' => $buttonText,
						'callback_data' => (string)$buttonData,
					];
				}
			}
		} else {
			$lineButton = [];

			foreach ($buttons as $buttonText => $buttonData) {
				if (preg_match('/^(https?\:\/\/|tg\:\/\/)/', $buttonData)) {
					$lineButton[][] = [
						'text' => $buttonText,
						'url' => $buttonData,
						'callback_data' => (string)$buttonData,
					];
				} else {
					$lineButton[] = [
						'text' => $buttonText,
						'callback_data' => (string)$buttonData,
					];
				}
			}

			$inlineKeyboard[] = $lineButton;
		}

		if ($this->paginationMarkUp) {
			$inlineKeyboard[] = $this->paginationMarkUp;
		}

		if ($this->predefinedMarkUp) {
			$inlineKeyboard[] = $this->predefinedMarkUp;
		}

		return $inlineKeyboard;
	}

	private function request($method, $parameters = []) {
		if (!is_string($method)) {
			throw new Exception("Method name must be a string");
		}

		if (!is_array($parameters)) {
			throw new Exception("Parameters must be an array");
		}

		$handle = curl_init("https://api.telegram.org/bot{$this->key}/" . $method);
		curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($handle, CURLOPT_TIMEOUT, 60);
		curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($parameters));
		curl_setopt($handle, CURLOPT_HTTPHEADER, [
			"Content-Type: application/json",
			"Retry-After: 3600",
		]);

		return $this->checkResponse($handle, $parameters);
	}

	private function checkResponse($handle, $parameters) {
		if (!$response = curl_exec($handle)) {
			$errorNumber = curl_errno($handle);
			$errorText = curl_error($handle);

			return [
				'status' => false,
				'error' => [
					'number' => $errorNumber,
					'message' => $errorText,
					'params' => $parameters,
				],
			];
		}

		$http_code = intval(curl_getinfo($handle, CURLINFO_HTTP_CODE));
		curl_close($handle);

		if ($http_code == 200) {
			return [
				'status' => true,
				'data' => $response,
			];
		}

		$responseObject = json_decode($response, true);

		return [
			'status' => false,
			'error' => [
				'number' => $responseObject['error_code'],
				'message' => $responseObject['description'],
				'params' => $parameters,
			],
		];
	}
}