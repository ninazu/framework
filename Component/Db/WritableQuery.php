<?php

namespace vendor\ninazu\framework\Component\Db;

use RuntimeException;

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

	 */
	public function setTable($tableName) {
		if (!is_string($tableName)) {
			throw new RuntimeException('Wrong value of tableName');
		}

		$this->table = " {$tableName}";

		return $this;
	}

	/**@internal */
	public function affectedRows() {
		return $this->affectedRows;
	}

	abstract public function priority($scenario);
}