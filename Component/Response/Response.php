<?php

namespace vendor\ninazu\framework\Component\Response;

use RuntimeException;
use vendor\ninazu\framework\Component\Response\Serializer\CsvSerializer;
use vendor\ninazu\framework\Component\Response\Serializer\HtmlSerializer;
use vendor\ninazu\framework\Component\Response\Serializer\JsonSerializer;
use vendor\ninazu\framework\Core\BaseComponent;
use vendor\ninazu\framework\Core\Environment;
use vendor\ninazu\framework\Component\Encoder\IEncoder;
use vendor\ninazu\framework\Helper\Reflector;

class Response extends BaseComponent implements IResponse {

	protected $contentType = self::CONTENT_JSON;

	protected $forceHttpStatus = true;

	protected $authSchema = self::AUTH_BEARER;

	protected $serializers = [
		self::CONTENT_HTML => [
			'class' => HtmlSerializer::class,
		],
		self::CONTENT_JSON => [
			'class' => JsonSerializer::class,
		],
		self::CONTENT_CSV => [
			'class' => CsvSerializer::class,
		],
	];

	/**@var IEncoder */
	protected $extraEncoder;

	private $statusCode;

	private $headers = [];

	private $notifications = [];

	private $data;

	private $extra = [];

	public function init() {
		parent::init();

		if (Environment::isCLI()) {
			$this->forceHttpStatus = false;
		}

		if ($this->extraEncoder) {
			$className = $this->extraEncoder['class'];
			$config = $this->extraEncoder['config'];
			$this->extraEncoder = new $className($this->getApplication(), $config);
		}
	}

	public function addExtra(array $extra) {
		$this->extra[] = array_merge($this->extra, $extra);
	}

	public function addNotify($typeEnum, $message, array $extra = []) {
		$this->notifications[] = [
			'type_enum' => $typeEnum,
			'message' => $message,
			'extra' => $extra,
		];
	}

	/**
	 * @param $errorCode
	 * @param $data
	 * @param array $extra
	 *
	 * @return bool
	 */
	public function sendError($errorCode, $data, array $extra = []) {
		$this->setStatusCode($errorCode);
		$this->setData($data);
		$encoder = $this->getApplication()->encoder;

		if ($this->extraEncoder && $extra && $encoder->hasKey()) {
			$extra = [
				'info' => $this->getApplication()->encoder->encode(print_r($extra, true)),
			];
		}

		$this->addExtra($extra);
		$this->render();
		$this->end(self::EXIT_CODE_WITH_ERROR);

		return false;
	}

	/**
	 * @param $data
	 *
	 * @return bool
	 */
	public function sendOk($data) {
		$this->setStatusCode(self::STATUS_CODE_OK);
		$this->setData($data);
		$this->render();
		$this->end(self::EXIT_CODE_OK);

		return true;
	}

	public function setContentType($type) {
		$this->contentType = $type;

		return $this;
	}

	/**
	 * @param $data
	 *

	 */
	public function setHeaders(array $data) {
		foreach ($data as $key => $value) {
			$this->headers[$key] = $value;
		}
	}

	public function removeHeaders() {
		$this->headers = [];
	}

	protected function setStatusCode($code) {
		$this->statusCode = $code;

		return $this;
	}

	protected function setData($params) {
		if (is_array($params)) {
			$this->data = array_replace_recursive(is_array($this->data) ? $this->data : [], $params);
		} else {
			$this->data = $params;
		}

		return $this;
	}

	/**
	 */
	protected function render() {
		$response = $this->serializeBody();
		$this->renderHeaders($response);

		echo $response;
	}

	/**
	 * @return string
	 */
	protected function serializeBody() {
		if (!array_key_exists($this->contentType, $this->serializers)) {
			throw new RuntimeException("Not implemented yet. Please set Serializer for '{$this->contentType}'");
		}

		$serializer = $this->serializers[$this->contentType];
		$className = $serializer['class'];
		$config = [];

		if (isset($serializer['config'])) {
			$config = $serializer['config'];
		}

		if (!Reflector::isInstanceOf($className, BaseSerializer::class)) {
			throw new RuntimeException("{$className} must be implemented of " . BaseSerializer::class);
		}

		/**@var BaseSerializer $serializer */
		$serializer = new $className($this->getApplication(), $config);

		return $serializer->serialize();
	}

	/**
	 * @param $response
	 *

	 */
	protected function renderHeaders($response) {
		if (!$this->forceHttpStatus && !Environment::isCLI()) {
			$protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
			header("{$protocol} {$this->statusCode}");
		}

		$this->setHeaders([
			'Content-Type' => $this->contentType,
			'Content-Length' => strlen($response),
		]);

		if (!Environment::isCLI()) {
			foreach ($this->headers as $header => $value) {
				header("{$header}: {$value}");
			}
		}
	}

	protected function end($exitCode) {
		exit($exitCode);
	}

	public function getAuthSchema() {
		return $this->authSchema;
	}

	public function getExtra() {
		return $this->extra;
	}

	public function getData() {
		return $this->data;
	}

	public function getStatusCode() {
		return $this->statusCode;
	}

	public function forceHttpStatus() {
		return $this->forceHttpStatus;
	}

	public function getNotifications() {
		return $this->notifications;
	}
}