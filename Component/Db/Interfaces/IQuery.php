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
	 * Сan contain SQL injection
	 *
	 * @param string $sql
	 * @param bool $withPlaceholders
	 *
	 * @return IQueryPrepare
	 */
	public function getSQL(&$sql, $withPlaceholders = true);
}