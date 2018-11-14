<?php

namespace vendor\ninazu\framework\Component\Response\Serializer;

use vendor\ninazu\framework\Component\Response\BaseSerializer;

class HtmlSerializer extends BaseSerializer {

	public function serialize() {
		$app = $this->getApplication();
		$response = $app->response;
		$data = $response->getData();
		$extra = null;

		if ($response->getExtra()) {
			$extra = "<ul>\n\t<li>" . implode("</li>\n\t<li>", $response->getExtra()) . "</li>\n</ul>";
		}

		return $data . $extra;
	}
}