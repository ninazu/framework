<?php

namespace vendor\ninazu\framework\Core;

abstract class BaseComponent extends BaseConfigurator {

	private $application;

	/**
	 * @param BaseApplication $application
	 * @param array $config
	 */
	public function __construct(BaseApplication $application, array $config) {
		$this->fillFromConfig($config);
		$this->application = $application;

		//SubConstructor
		$this->init();
	}

	/**
	 * @internal
	 */
	protected function init() {
	}

	/**
	 * @return BaseApplication
	 */
	protected function getApplication() {
		return $this->application;
	}
}