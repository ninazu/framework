<?php

namespace vendor\ninazu\framework\Core\Handler;

use Exception;

interface IHandler {

	public function handlerAutoLoader($class);

	public function handlerError($error_code, $message, $file = null, $line = null);

	public function handlerShutdown();

	public function handlerException(Exception $exception);

	public function setAutoLoader($autoLoaders);
}