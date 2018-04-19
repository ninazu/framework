<?php

namespace vendor\ninazu\framework\Component\Db;

class Meta {

	private $meta;

	/**
	 * @internal
	 * @param array $meta
	 */
	public function __construct(array $meta) {
		$this->meta = $meta;
	}

	public function convert($key, $row) {
		$value = $row[$key];
		$smartConverter = $this->meta[$key]['smartConvert'];

		return $smartConverter($value);
	}
}