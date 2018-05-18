<?php

namespace vendor\ninazu\framework\Component;

use vendor\ninazu\framework\Core\BaseComponent;
use vendor\ninazu\framework\Core\Environment;

class Request extends BaseComponent {

	const METHOD_GET = 'GET';

	const METHOD_POST = 'POST';

	const METHOD_PATCH = 'PATCH';

	const METHOD_PUT = 'PUT';

	const METHOD_DELETE = 'DELETE';

	private $URL;

	private $headers;

	private $path;

	private $params = [];

	public function getUrl() {
		return $this->URL;
	}

	public function getPath() {
		return $this->URL;
	}

	public function getParams() {
		return $this->params;
	}

	public function getHeaders() {
		return $this->params;
	}

	public function getMethod() {
		//TODO Console?

		return isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : self::METHOD_GET;
	}

	protected function init() {
		if (Environment::isCli()) {
			global $argv;

			if (isset($argv[1])) {
				$this->URL = $argv[1];
			} else {
				$this->URL = '/';
			}
		} else {
			$this->URL = $_SERVER['REQUEST_URI'];
		}

		$headers = [];

		//Apache
		if (function_exists('getallheaders')) {
			$headers = getallheaders();
		} else { //NGinx
			foreach ($_SERVER as $name => $value) {
				if (substr($name, 0, 5) == 'HTTP_') {
					$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
				} else {
					$headers[$name] = $value;
				}
			}
		}

		$this->headers = $headers;

		$URLParts = parse_url($this->URL);
		$this->path = $URLParts['path'];

		if (isset($URLParts['query'])) {
			parse_str($URLParts['query'], $this->params);
		}
	}
}