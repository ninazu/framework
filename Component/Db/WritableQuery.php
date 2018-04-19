<?php

namespace vendor\ninazu\framework\Component\Db;

use ErrorException;

abstract class WritableQuery extends Query {

	protected $onError;

	protected $table;

	protected $affectedRows = 0;

	protected $priority;

	/**@internal
	 *
	 * @param string $tableName
	 *
	 * @return $this
	 *
	 * @throws ErrorException
	 */
	public function setTable($tableName) {
		if (!is_string($tableName)) {
			throw new ErrorException('Wrong value of tableName');
		}

		$this->table = $tableName;

		return $this;
	}

	/**@internal */
	public function affectedRows() {
		return $this->affectedRows;
	}

	abstract public function priority($scenario);
}