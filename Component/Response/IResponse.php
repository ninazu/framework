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

	const STATUS_CODE_PRECONDITION_FAILED = 412;

	const STATUS_CODE_REDIRECT = 302;

	const NOTIFY_TYPE_INFO = 1;

	const NOTIFY_TYPE_WARNING = 2;

	const NOTIFY_TYPE_ERROR = 3;

	public function getData();

	public function getExtra();

	public function getNotifications();

	public function getStatusCode();

	public function forceHttpStatus();

	public function addNotify($typeEnum, $message, $extra = []);

	public function setHeaders(array $data);

	public function sendError($errorCode, $data, $extra = []);

	public function sendOk($data);
}