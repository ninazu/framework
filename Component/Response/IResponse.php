<?php

namespace vendor\ninazu\framework\Component\Response;

interface IResponse {

	const STATUS_CODE_OK = 200;

	const STATUS_CODE_NOT_FOUND = 404;

	const STATUS_CODE_FORBIDDEN = 403;

	const STATUS_CODE_BAD_REQUEST = 400;

	const STATUS_CODE_LOGOUT = 401;

	const STATUS_CODE_SERVER_ERROR = 500;

	const STATUS_CODE_VARIANT_ALSO_NEGOTIATES = 506;

	const STATUS_CODE_VALIDATION = 422;

	const STATUS_CODE_PRECONDITION_FAILED = 412;

	const STATUS_CODE_REDIRECT = 302;

	const NOTIFY_TYPE_INFO = 1;

	const NOTIFY_TYPE_WARNING = 2;

	const NOTIFY_TYPE_ERROR = 3;

	const EXIT_CODE_OK = 0;

	const EXIT_CODE_WITH_ERROR = 1;

	const CONTENT_JSON = 'application/json';

	const CONTENT_CSV = 'text/csv';

	const CONTENT_HTML = 'text/html';

	const CONTENT_PLAIN = 'text/plain';

	const AUTH_BASIC = 'Basic';

	const AUTH_BEARER = 'Bearer';

	const AUTH_DIGEST = 'Digest';

	const AUTH_HOBA = 'HOBA';

	const AUTH_MUTUAL = 'Mutual';

	const AUTH_NEGOTIATE = 'Negotiate';

	const AUTH_OAUTH = 'OAuth';

	const AUTH_SCRAM_SHA_1 = 'SCRAM-SHA-1';

	const AUTH_SCRAM_SHA_256 = 'SCRAM-SHA-256';

	const AUTH_VAPID = 'vapid';

	public function getAuthSchema();

	public function getData();

	public function getExtra();

	public function getNotifications();

	public function getStatusCode();

	public function forceHttpStatus();

	public function addNotify($typeEnum, $message, array $extra = []);

	public function addExtra(array $extra);

	public function setHeaders(array $data);

	public function sendError($errorCode, $data, array $extra = []);

	public function sendOk($data);
}