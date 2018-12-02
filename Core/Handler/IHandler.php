<?php

namespace vendor\ninazu\framework\Core\Handler;

use Throwable;

interface IHandler {

	public function handlerAutoLoader($class);

	public function handlerError($error_code, $message, $file = null, $line = null);

	public function handlerShutdown();

	public function handlerException(Throwable $exception);

	public function setAutoLoader($autoLoaders);
}