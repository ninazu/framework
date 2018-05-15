<?php

namespace vendor\ninazu\framework\Component\Db\Interfaces;

use vendor\ninazu\framework\Component\Db\Expression;

interface IUpdate extends IBasicQuery {

	/**
	 * @return $this
	 */
	public function lowPriority();

	/**
	 * @return $this
	 */
	public function ignoreError();

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
	 * @internal
	 *
	 * USE ONLY FOR DEBUG. Сan contain SQL injection or wrong replaced value
	 *
	 * @param string $sql
	 * @param bool $withPlaceholders
	 *
	 * @return IUpdatePrepare
	 */
	public function getSQL(&$sql, $withPlaceholders);
}