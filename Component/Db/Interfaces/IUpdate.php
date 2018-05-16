<?php

namespace vendor\ninazu\framework\Component\Db\Interfaces;

use vendor\ninazu\framework\Component\Db\Expression;

interface IUpdate extends IBasicQuery {

	const ON_ERROR_IGNORE = ' IGNORE';

	const PRIORITY_LOW = ' LOW_PRIORITY';

	/**
	 * @return $this
	 */
	public function lowPriority();

	/**
	 * @return $this
	 */
	public function ignoreErrors();

	/**
	 * @param Expression[] $sequence
	 *
	 * @return $this
	 */
	public function orderBy(array $sequence);

	/**
	 * @param int $count
	 *
	 * @return $this
	 */
	public function limit($count);

	/**
	 * @return IUpdateResult
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
	 * @return IUpdatePrepare
	 */
	public function getSQL(&$sql, $withPlaceholders);
}