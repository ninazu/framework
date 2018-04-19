<?php

namespace vendor\ninazu\framework\Component\Db\Interfaces;

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
	 * @param array $sequence
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
	 * Сan contain SQL injection
	 *
	 * @param string $sql
	 * @param bool $withPlaceholders
	 *
	 * @return IUpdatePrepare
	 */
	public function getSQL(&$sql, $withPlaceholders = true);
}