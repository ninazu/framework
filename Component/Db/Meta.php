<?php

namespace vendor\ninazu\framework\Component\Db;

class Meta {

	private $meta;

	private $keys;

	/**
	 * @internal
	 * @param array $meta
	 */
	public function __construct(array $meta) {
		$this->keys = array_keys($meta);
		$this->meta = $meta;
	}

	public function getKeys() {
		return $this->keys;
	}

	public function convert($key, $row) {
		$value = $row[$key];
		$smartConverter = $this->meta[$key]['smartConvert'];

		return $smartConverter($value);
	}
}