<?php

namespace vendor\ninazu\framework\Component\Response;

interface IResponse {

	const STATUS_CODE_OK = 200;

	const STATUS_CODE_NOT_FOUND = 404;

	const STATUS_CODE_FORBIDDEN = 403;

	const STATUS_CODE_BAD_REQUEST = 400;

	const STATUS_CODE_LOGOUT = 401;

	const STATUS_CODE_SERVER_ERROR = 500;

	const STATUS_CODE_VALIDATION = 422;

	const STATUS_PRECONDITION_FAILED = 412;

	public function getData();

	public function getStatusCode();

	public function forceHttpStatus();

	public function setHeaders(array $data);

	public function sendError($errorCode, $data);

	public function sendOk($data);
}