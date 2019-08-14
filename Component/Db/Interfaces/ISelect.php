<?php

namespace vendor\ninazu\framework\Component\Db\Interfaces;

interface ISelect extends IBasicQuery {

	/**
	 * @return $this
	 */
	public function updateQuery(string $query);

	/**
	 * @return ISelectResult
	 */
	public function execute();

	/**
	 * USE ONLY FOR DEBUG. Сan contain SQL injection or wrong replaced value
	 *
	 * @param string $sql
	 * @param bool $withPlaceholders
	 *
	 * @return ISelectPrepare
	 *
	 * @internal
	 *
	 * @see \vendor\ninazu\framework\Component\Db\Query
	 */
	public function getSQL(&$sql, $withPlaceholders);
}