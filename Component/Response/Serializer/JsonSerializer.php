<?php

namespace vendor\ninazu\framework\Component\Response\Serializer;

use vendor\ninazu\framework\Component\Response\IResponse;
use vendor\ninazu\framework\Component\Response\BaseSerializer;
use vendor\ninazu\framework\Core\Environment;

class JsonSerializer extends BaseSerializer {

	public function serialize() {
		$response = $this->getApplication()->response;

		switch ($response->getStatusCode()) {
			case IResponse::STATUS_CODE_OK:
				$result = [
					'status' => true,
					'data' => $response->getData(),
				];
				break;

			case IResponse::STATUS_CODE_LOGOUT:
				$uniqueID = uniqid();
				$response->setHeaders([
					'WWW-Authenticate' => "{$response->getAuthSchema()} realm='{$uniqueID}', error='invalid_token'",
				]);

				$result = ([
					'status' => false,
				]);
				break;

			case IResponse::STATUS_CODE_NOT_FOUND:
			case IResponse::STATUS_CODE_FORBIDDEN:
			case IResponse::STATUS_CODE_PRECONDITION_FAILED:
				$result = ([
					'status' => false,
				]);
				break;

			case IResponse::STATUS_CODE_BAD_REQUEST:
				$result = [
					'status' => false,
					'fields' => $response->getData(),
				];
				break;

			case IResponse::STATUS_CODE_VALIDATION:
				$result = [
					'status' => false,
					'fields' => $response->getData(),
				];
				break;

			case IResponse::STATUS_CODE_SERVER_ERROR:
				if (!Environment::isInitialized() || Environment::isProduction()) {
					$result = [
						'status' => false,
						'message' => $response->getData(),
						'extra' => [],
					];
				} else {
					$result = [
						'status' => false,
						'message' => $response->getData(),
						'extra' => $response->getExtra(),
					];
				}
				break;

			default:
				$result = [
					'status' => false,
					'unexpected' => true,
				];
		}

		if ($response->forceHttpStatus()) {
			unset($result['status']);
		} else {
			$result['httpStatus'] = $response->getStatusCode();
		}

		if ($notifications = $response->getNotifications()) {
			$result['notifications'] = $notifications;
		}

		return json_encode($result);
	}
}