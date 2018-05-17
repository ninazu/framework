<?php

namespace vendor\ninazu\framework\Component\Db\Interfaces;

use vendor\ninazu\framework\Component\Db\Expression;

interface IDelete extends IBasicQuery {

	const PRIORITY_LOW = ' LOW_PRIORITY';

	const ON_ERROR_IGNORE = ' IGNORE';

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
	 * @return $this
	 */
	public function lowPriority();

	/**
	 * NOT IMPLEMENTED YET
	 *
	 * @internal
	 *
	 * @param array $partitions
	 * @return $this
	 */
	public function partitions(array $partitions);

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