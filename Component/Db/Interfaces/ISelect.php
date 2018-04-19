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
	 * Сan contain SQL injection
	 *
	 * @param string $sql
	 * @param bool $withPlaceholders
	 *
	 * @return ISelectPrepare
	 */
	public function getSQL(&$sql, $withPlaceholders = true);
}