<?php

namespace vendor\ninazu\framework\Core\Handler;

use Error;
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
	 * @throws Exception
	 */
	public function handlerError($error_code, $message, $file = null, $line = null) {
		if ($error_code) {
			throw new Exception($message, $error_code);
		}

		return true;
	}

	/**
	 */
	public function handlerShutdown() {
		$error = error_get_last();

		if ($error) {
			$this->handlerException(new Error($error['message'], $error['type']));
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
//			$args = [];

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
			return $this->application->response->sendError(Response::STATUS_CODE_SERVER_ERROR, $data, $extra);
		}

		return true;
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