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
	 * @return array
	 */
	public function queryAll();

	/**
	 * @return array
	 */
	public function queryOne();

	/**
	 * @param string $name
	 * @return mixed
	 */
	public function queryValue($name = null);
}