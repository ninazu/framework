<?php

namespace vendor\ninazu\framework\Component\Telegram;

use Exception;
use vendor\ninazu\framework\Component\Telegram\exception\NotFoundException;
use vendor\ninazu\framework\Component\Telegram\exception\NotModifiedException;
use vendor\ninazu\framework\Component\Telegram\exception\UnauthorizedException;
use vendor\ninazu\framework\Core\BaseComponent;

class TelegramBot extends BaseComponent {

	private $predefinedMarkUp;

	private $paginationMarkUp;

	protected $key;

	protected $secureParam;

	protected $webHookUrl;

	protected $botName;

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
				'inline_keyboard' => $this->prepareButtons($buttons, $inline),
			],
		];

		return $this->request('sendMessage', $params);
	}

	public function updateMarkUp($chatID, $messageID, $text, $buttons, $inline = false) {
		if (empty($messageID)) {
			return $this->markUp($chatID, $text, $buttons, $inline);
		}

		try {
			$response = $this->request('editMessageText', [
					'chat_id' => $chatID,
					'message_id' => $messageID,
					'parse_mode' => 'HTML',
					'text' => $text,
					'reply_markup' => [
						'inline_keyboard' => $this->prepareButtons($buttons, $inline),
					],
				]
			);
		} catch (NotFoundException $exception) {
			$response = $this->request('editMessageText', [
					'chat_id' => $chatID,
					'message_id' => null,
					'parse_mode' => 'HTML',
					'text' => $text,
					'reply_markup' => [
						'inline_keyboard' => $this->prepareButtons($buttons, $inline),
					],
				]
			);
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

	private function prepareButtons($buttons, $inline) {
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
		curl_setopt($handle, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);

		return $this->checkResponse($handle, $parameters);
	}

	private function checkResponse($handle, $parameters = []) {
		if (!$response = curl_exec($handle)) {
			$errorNumber = curl_errno($handle);
			$errorText = curl_error($handle);

			throw new Exception("Curl returned error {$errorNumber}: {$errorText}");
		}

		$http_code = intval(curl_getinfo($handle, CURLINFO_HTTP_CODE));
		curl_close($handle);

		if ($http_code == 200) {
			return $response;
		}

		$responseObject = json_decode($response, true);

		if ($http_code >= 500) {
			sleep(100);
			throw new Exception("Server side error. {$responseObject['error_code']}: {$responseObject['description']}");
		} elseif ($http_code == 401) {
			throw new UnauthorizedException();
		} elseif ($http_code == 429) {
			sleep(30);

			return $response;
		} elseif ($http_code == 400) {
			print_r($responseObject);

			switch ($responseObject['description']) {
				case "Bad Request: message is not modified":
					throw new NotModifiedException($responseObject['error_code']);

				case "Bad Request: message to edit not found":
					throw new NotFoundException($responseObject['error_code']);
			}
		} else {
			echo base64_encode(print_r([$parameters], true));

			throw new Exception("Request has failed with error {$responseObject['error_code']}: {$responseObject['description']}.");
		}
	}
}