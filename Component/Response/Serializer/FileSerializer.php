<?php

namespace vendor\ninazu\framework\Component\Response\Serializer;

use vendor\ninazu\framework\Component\Response\BaseSerializer;

class FileSerializer extends BaseSerializer {

	protected $filename;

	public function serialize() {
		$app = $this->getApplication();
		$response = $app->response;
		$data = $response->getData();
		$extra = $response->getExtra();

		if (!empty($data) || !empty($extra)) {
			file_put_contents($this->filename, json_encode([
				'data' => $data,
				'extra' => $extra,
			], JSON_PRETTY_PRINT));
		}

		return '';
	}
}