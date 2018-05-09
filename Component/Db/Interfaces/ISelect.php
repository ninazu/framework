<?php

namespace vendor\ninazu\framework\Component\Db\Interfaces;

interface ISelect extends IBasicQuery {

	/**
	 * @return ISelectResult
	 */
	public function execute();

	/**
	 * @internal
	 *
	 * USE ONLY FOR DEBUG. Сan contain SQL injection or wrong replaced value
	 *
	 * @param string $sql
	 * @param bool $withPlaceholders
	 *
	 * @return ISelectPrepare
	 *
	 * @see \vendor\ninazu\framework\Component\Db\Query
	 */
	public function getSQL(&$sql, $withPlaceholders);
}