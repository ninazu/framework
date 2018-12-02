<?php

namespace vendor\ninazu\framework\Component\Response\Serializer;

use vendor\ninazu\framework\Component\Response\BaseSerializer;

class EmailSerializer extends BaseSerializer {

	public function serialize() {
		$app = $this->getApplication();
		$response = $app->response;
		$data = $response->getData();
		$extra = $response->getExtra();

		if (!empty($data) || !empty($extra)) {
			$app->mail->send([$app->getAdminEmail(),], 'Email Serializer', json_encode([
				'data' => $data,
				'extra' => $extra,
			], JSON_PRETTY_PRINT));
		}

		return '';
	}
}