<?php

namespace vendor\ninazu\framework\Component\Response\Serializer;

use vendor\ninazu\framework\Component\Response\IResponse;
use vendor\ninazu\framework\Component\Response\Serializer;
use vendor\ninazu\framework\Core\Environment;

class JsonSerializer extends Serializer {

	public function serialize() {
		$response = $this->getApplication()->response;

		switch ($response->getStatusCode()) {
			case IResponse::STATUS_CODE_OK:
				$result = [
					'status' => true,
					'data' => $response->getData(),
				];
				break;

			case IResponse::STATUS_CODE_NOT_FOUND:
			case IResponse::STATUS_CODE_FORBIDDEN:
			case IResponse::STATUS_CODE_LOGOUT:
			case IResponse::STATUS_PRECONDITION_FAILED:
				$result = ([
					'status' => false,
				]);
				break;

			case IResponse::STATUS_CODE_BAD_REQUEST:
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
						'message' => $response->getData()['message'],
					];
				} else {
					$result = [
						'status' => false,
						'message' => $response->getData()['message'],
						'location' => $response->getData()['location'],
					];
				}
				break;

			default:
				$result = [
					'status' => false,
					'unexpected' => true,
				];
		}

		if (!$response->forceHttpStatus()) {
			$result['httpStatus'] = $response->getStatusCode();
		}

		return json_encode($result);
	}
}