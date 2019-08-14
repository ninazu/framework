<?php

namespace vendor\ninazu\framework\Component\Db\Interfaces;

interface IQueryResult {

	/**
	 * @return int
	 */
	public function columnCount();

	/**
	 * @return int
	 */
	public function rowCount();

	/**
	 * @param int $columnIndex
	 *
	 * @return array|false
	 */
	public function columnMeta(int $columnIndex);
}