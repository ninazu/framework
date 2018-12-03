<?php

namespace vendor\ninazu\framework\Component\Telegram\v2;

use vendor\ninazu\framework\Core\BaseComponent;

/**
 * @property Request $request
 * @property Response $response
 */
class Bot extends BaseComponent {

	public $request;

	public $response;

	protected $key;

	protected $secureParam;

	protected $webHookUrl;

	protected $botName;

	protected $debug = false;

	public function init() {
		$content = file_get_contents("php://input");

		$this->request = new Request($content);
		$this->response = new Response();
		$this->response->fillFromConfig($this->config);
	}

	public function getSecureParam() {
		return $this->secureParam;
	}
}