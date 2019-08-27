<?php

namespace vendor\ninazu\framework\Component\Db\Interfaces;

interface IInsert extends IBasicQuery {

	//const ON_DUPLICATE_IGNORE = ' IGNORE';

	const ON_DUPLICATE_UPDATE = 'ON DUPLICATE KEY UPDATE';

	const PRIORITY_LOW = ' LOW_PRIORITY';

	const PRIORITY_HIGH = ' HIGH_PRIORITY';

	const PRIORITY_DELAYED = ' DELAYED';

	/**
	 * @param array $columnUpdate
	 *
	 * @return $this
	 */
	public function onDuplicateUpdate(array $columnUpdate);

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
	 * USE ONLY FOR DEBUG. Сan contain SQL injection or wrong replaced value
	 *
	 * @internal
	 *
	 * @param string $sql
	 * @param bool $withPlaceholders
	 *
	 * @return IInsertPrepare
	 */
	public function getSQL(&$sql, $withPlaceholders);
}