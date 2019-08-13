<?php

namespace vendor\ninazu\framework\Component\Db\Interfaces;

interface ISelectResult extends IQueryResult {

	/**
	 * @param string $columnName
	 *
	 * @return $this
	 */
	public function indexBy($columnName);

	/**
	 * @param callable($row, $prevValue, Meta $meta):array $callback
	 *
	 * @return $this
	 */
	public function handler(callable $callback);

	/**
	 * @return $this
	 */
	public function convertMeta();

	/**
	 * @return array
	 */
	public function queryAll(): array;

	/**
	 * @return array
	 */
	public function queryOne(): array;

	/**
	 * @param string $name
	 * @return mixed
	 */
	public function queryValue($name = null);
}