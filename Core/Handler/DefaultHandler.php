<?php

namespace vendor\ninazu\framework\Core\Handler;

use RuntimeException;
use Exception;
use Throwable;
use vendor\ninazu\framework\Component\Response\Response;
use vendor\ninazu\framework\Core\BaseApplication;
use vendor\ninazu\framework\Core\Environment;

require_once __DIR__ . '/IHandler.php';
require_once __DIR__ . '/AutoLoader.php';

/**
 * @property BaseApplication $application
 */
class DefaultHandler implements IHandler {

	private $application;

	public function __construct(BaseApplication $application) {
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
	 *
	 * @return bool
	 *

	 */
	public function handlerError($error_code, $message, $file = null, $line = null) {
		if ($error_code) {
			throw new RuntimeException($message, $error_code, 0, $file, $line);
		}

		return true;
	}

	/**
	 
	 */
	public function handlerShutdown() {
		$error = error_get_last();

		if ($error) {
			$this->handlerException(new RuntimeException($error['message'], $error['type'], 0, $error['file'], $error['line']));
		}

		exit(0);
	}

	public function handlerException(Throwable $exception) {
		if (ob_get_length()) {
			ob_end_clean();
		}

		try {
			Environment::getEnvironment();
		} catch (Exception $exception) {
			$data = $exception->getMessage();
		}

		$extra = [];

		if (isset($data)) {
			//EnvironmentException
		} else {
			$args = [];

			foreach ($exception->getTrace() as $row) {
				if (isset($row['file'], $row['line'])) {
					$extraRow = "{$row['file']}:{$row['line']}";

//					if (isset($row['function'])) {
//						$args[] = "\n\t{$row['function']}(" . (isset($row['args']) ? implode(',', $row['args']) : '') . ')';
//					}

					$extra[] = $extraRow;
				}
			}

			if (Environment::isProduction()) {
				$md5 = md5($exception->getMessage()) . md5(implode($extra));
				$data = "Unexpected server error {$md5}";

				if ($this->application->getAdminEmail()) {
					$this->application->mail->send([$this->application->getAdminEmail()], "DEBUG {$md5}", print_r([
						$exception->getMessage(),
						$extra,
					], true));
				}

				$extra = [];
			} else {
				$data = $exception->getMessage();
			}
		}

		if (!headers_sent()) {
			$this->application->response->sendError(Response::STATUS_CODE_SERVER_ERROR, $data, $extra);
		}
	}

	/**
	 * @param $autoLoaders
	 *
	 
	 */
	public function setAutoLoader($autoLoaders) {
		foreach ($autoLoaders as $namespace => $paths) {
			Autoloader::addSources("{$namespace}\\", $paths);
		}
	}
}