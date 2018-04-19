<?php

namespace vendor\ninazu\framework\Component;

use Exception;
use vendor\ninazu\framework\Core\Component;
use vendor\ninazu\framework\Core\Environment;

class Response extends Component {

	const EXIT_CODE_OK = 0;

	const EXIT_CODE_WITH_ERROR = 1;

	const STATUS_CODE_OK = 200;

	const STATUS_CODE_NOT_FOUND = 404;

	const STATUS_CODE_FORBIDDEN = 403;

	const STATUS_CODE_LOGOUT = 401;

	const STATUS_CODE_SERVER_ERROR = 500;

	const STATUS_CODE_VALIDATION = 422;

	const CONTENT_JSON = 'application/json';

	protected $contentType = self::CONTENT_JSON;

	protected $httpStatus = true;

	private $statusCode;

	private $headers = [];

	private $data;

	/**
	 * @param $errorCode
	 * @param $data
	 *
	 * @throws Exception
	 */
	public function sendError($errorCode, $data) {
		$this->setStatusCode($errorCode);
		$this->setData($data);
		echo $this->render();
		$this->end(self::EXIT_CODE_WITH_ERROR);
	}

	/**
	 * @param $data
	 * @throws Exception
	 */
	public function sendOk($data) {
		$this->setStatusCode(self::STATUS_CODE_OK);
		$this->setData($data);
		$this->render();
		$this->end(self::EXIT_CODE_OK);
	}

	protected function setStatusCode($code) {
		$this->statusCode = $code;
	}

	protected function setData($params) {
		if (is_array($params)) {
			$this->data = array_replace_recursive(is_array($this->data) ? $this->data : [], $params);
		} else {
			$this->data = $params;
		}
	}

	/**
	 * @throws Exception
	 */
	protected function render() {
		$response = $this->serializeBody();
		$this->renderHeaders($response);

		echo $response;
	}

	/**
	 * @return string
	 * @throws Exception
	 */
	protected function serializeBody() {
		if ($this->contentType === self::CONTENT_JSON) {
			$response = $this->serializeJson();
		} else {
			throw new Exception('Not implemented yet');
		}

		return $response;
	}

	/**
	 * @return string
	 * @throws Exception
	 */
	protected function serializeJson() {
		switch ($this->statusCode) {
			case self::STATUS_CODE_OK:
				$response = [
					'status' => true,
					'data' => $this->data,
				];
				break;

			case self::STATUS_CODE_NOT_FOUND:
			case self::STATUS_CODE_FORBIDDEN:
			case self::STATUS_CODE_LOGOUT:
				$response = ([
					'status' => false,
				]);
				break;

			case self::STATUS_CODE_VALIDATION:
				$response = [
					'status' => false,
					'fields' => $this->data,
				];
				break;

			case self::STATUS_CODE_SERVER_ERROR:
				if (!Environment::isInitialized() || Environment::isProduction()) {
					$response = [
						'status' => false,
						'message' => $this->data['message'],
					];
				} else {
					$response = [
						'status' => false,
						'message' => $this->data['message'],
						'location' => $this->data['location'],
					];
				}
				break;

			default:
				$response = [
					'status' => false,
					'message' => $this->data,
				];
		}

		if (!$this->httpStatus) {
			$response['httpStatus'] = $this->statusCode;
		}

		return json_encode($response);
	}

	/**
	 * @param $response
	 * @throws Exception
	 */
	protected function renderHeaders($response) {
		if ($this->httpStatus) {
			$protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
			header("{$protocol} {$this->statusCode}");
		}

		$this->setHeaders([
			'Content-Type' => $this->contentType,
			'Content-Length' => strlen($response),
		]);

		foreach ($this->headers as $header => $value) {
			header("{$header}: {$value}");
		}
	}

	/**
	 * @param $data
	 * @throws Exception
	 */
	protected function setHeaders($data) {
		if (!is_array($data)) {
			throw new Exception('$data is not an array or string');
		}

		foreach ($data as $key => $value) {
			$this->headers[$key] = $value;
		}
	}

	protected function end($exitCode) {
		exit($exitCode);
	}
}