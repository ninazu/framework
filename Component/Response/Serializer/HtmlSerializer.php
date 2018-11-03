<?php

namespace vendor\ninazu\framework\Component\Response\Serializer;

use vendor\ninazu\framework\Component\Response\BaseSerializer;

class HtmlSerializer extends BaseSerializer {

	public function serialize() {
		$app = $this->getApplication();
		$response = $app->response;

		return $response->getData();
	}
}