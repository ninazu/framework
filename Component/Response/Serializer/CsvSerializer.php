<?php

namespace vendor\ninazu\framework\Component\Response\Serializer;

use vendor\ninazu\framework\Component\Response\Serializer;

class CsvSerializer extends Serializer {

	protected $title;

	protected $forceColumnName = false;

	public function serialize() {
		$app = $this->getApplication();
		$response = $app->response;

		$stream = fopen('data://text/plain,', 'w+');

		if (!empty($this->title)) {
			$fileTitle = $this->title;
		} else {
			$fileTitle = $app->request->getPath();
		}

		//TODO Sanitize
		$response->setHeaders([
			'Content-disposition' => "attachment; filename=\"{$fileTitle}.csv\"",
		]);

		$data = $response->getData();

		if (!empty($data[0])) {
			if (!empty($this->forceColumnName)) {
				fputcsv($stream, array_keys($data[0]));
			}

			foreach ($data as $val) {
				fputcsv($stream, $val);
			}

			rewind($stream);
		}

		return stream_get_contents($stream);
	}
}