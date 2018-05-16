<?php

namespace vendor\ninazu\framework\Component\Db\Interfaces;

interface IDelete extends IBasicQuery {

	const PRIORITY_LOW = ' LOW_PRIORITY';

	const ON_ERROR_IGNORE = ' IGNORE';

	/**
	 * @return $this
	 */
	public function lowPriority();

	/**
	 * @return $this
	 */
	public function ignoreErrors();

	/**
	 * @return IDeleteResult
	 */
	public function execute();

	/**
	 * USE ONLY FOR DEBUG. Сan contain SQL injection or wrong replaced value
	 *
	 * @internal
	 *
	 * @param string $sql
	 * @param bool $withPlaceholders
	 *
	 * @return IDeletePrepare
	 */
	public function getSQL(&$sql, $withPlaceholders);
}