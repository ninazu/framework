<?php

namespace vendor\ninazu\framework\Component\Db\Interfaces;

interface IQuery extends IBasicQuery {

	/**
	 * @return IQueryResult
	 */
	public function execute();

	/**
	 * USE ONLY FOR DEBUG. Сan contain SQL injection or wrong replaced value
	 *
	 * @internal
	 *
	 * @param string $query
	 * @param bool $withPlaceholders
	 *
	 * @return IQueryPrepare
	 */
	public function getSQL(&$query, $withPlaceholders);
}