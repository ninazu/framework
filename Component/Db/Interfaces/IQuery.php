<?php

namespace vendor\ninazu\framework\Component\Db\Interfaces;

interface IQuery extends IBasicQuery {

	/**
	 * @return IQueryResult
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
	 * @return IQueryPrepare
	 */
	public function getSQL(&$sql, $withPlaceholders = true);
}