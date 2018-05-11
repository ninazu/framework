<?php

namespace vendor\ninazu\framework\Core\Handler;

use ErrorException;
use Exception;
use vendor\ninazu\framework\Component\Response\Response;
use vendor\ninazu\framework\Core\Application;
use vendor\ninazu\framework\Core\Environment;

require_once __DIR__ . '/IHandler.php';
require_once __DIR__ . '/AutoLoader.php';

/**
 * @property Application $application
 */
class DefaultHandler implements IHandler {

	private $application;

	public function __construct(Application $application) {
		$this->application = $application;
	}

	public function handlerAutoLoader($class) {
		AutoLoader::loadClass($class);
	}

	/**
	 * @param $error_code
	 * @param $message
	 * @param null $file
	 * @param null $line
	 * @return bool
	 *
	 * @throws ErrorException
	 */
	public function handlerError($error_code, $message, $file = null, $line = null) {
		if ($error_code) {
			throw new ErrorException($message, $error_code, 0, $file, $line);
		}

		return true;
	}

	/**
	 * @throws Exception
	 */
	public function handlerShutdown() {
		$error = error_get_last();

		if ($error) {
			$this->handlerException(new ErrorException($error['message'], $error['type'], 0, $error['file'], $error['line']));
		}

		exit(0);
	}

	/**
	 * @param Exception $exception
	 *
	 * @throws Exception
	 */
	public function handlerException(Exception $exception) {
		//TODO BugTracker

		try {
			Environment::getEnvironment();
		} catch (Exception $exception) {
			$data = [
				'message' => $exception->getMessage(),
			];
		}

		if (isset($data)) {
			//EnvironmentException
		} elseif (Environment::isProduction()) {
			$data = [
				'message' => "Unexpected server error " . md5($exception->getMessage()),
			];
		} else {
			$data = [
				'message' => $exception->getMessage(),
				'location' => "{$exception->getFile()}:{$exception->getLine()}",
			];
		}

		$this->application->response->sendError(Response::STATUS_CODE_SERVER_ERROR, $data);
	}

	/**
	 * @param $autoLoaders
	 *
	 * @throws Exception
	 */
	public function setAutoLoader($autoLoaders) {
		foreach ($autoLoaders as $namespace => $paths) {
			Autoloader::addSources("{$namespace}\\", $paths);
		}
	}
}