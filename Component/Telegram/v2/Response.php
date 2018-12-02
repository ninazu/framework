<?php

namespace vendor\ninazu\framework\Component\Telegram\v2;

use TypeError;
use vendor\ninazu\framework\Core\BaseConfigurator;

class Response extends BaseConfigurator {

	protected $webHookUrl;

	protected $key;

	public function install() {
		return $this->request('setWebhook', [
			'url' => $this->webHookUrl,
		]);
	}

	private function request($method, $parameters = []) {
		if (!is_string($method)) {
			throw new TypeError("Method name must be a string");
		}

		if (!is_array($parameters)) {
			throw new TypeError("Parameters must be an array");
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