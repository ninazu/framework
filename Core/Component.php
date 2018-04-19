<?php

namespace vendor\ninazu\framework\Core;

abstract class Component extends Configurator {

	private $application;

	/**
	 * @param Application $application
	 * @param array $config
	 *
	 * @throws \ReflectionException
	 */
	public function __construct(Application $application, array $config) {
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
	 * @return Application
	 */
	protected function getApplication() {
		return $this->application;
	}
}