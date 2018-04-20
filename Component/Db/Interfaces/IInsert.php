<?php

namespace vendor\ninazu\framework\Component\Db\Interfaces;

interface IInsert extends IBasicQuery {

	const ON_DUPLICATE_IGNORE = ' IGNORE';

	const ON_DUPLICATE_UPDATE = 'ON DUPLICATE KEY UPDATE';

	const PRIORITY_LOW = ' LOW_PRIORITY';

	const PRIORITY_HIGH = ' HIGH_PRIORITY';

	const PRIORITY_DELAYED = ' DELAYED';

	/**
	 * @param string $scenario
	 * @param array $columnUpdate
	 *
	 * @return $this
	 */
	public function onDuplicate($scenario, $columnUpdate = []);

	/**
	 * @param string $scenario
	 *
	 * @return $this
	 */
	public function priority($scenario);

	/**
	 * @return IInsertResult
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
	 * @return IInsertPrepare
	 */
	public function getSQL(&$sql, $withPlaceholders = true);
}