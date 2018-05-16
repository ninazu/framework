<?php

namespace vendor\ninazu\framework\Component\Db;

use ErrorException;
use vendor\ninazu\framework\Component\Db\Interfaces\IDelete;
use vendor\ninazu\framework\Component\Db\Interfaces\IDeleteResult;
use vendor\ninazu\framework\Helper\Formatter;

class DeleteQuery extends WritableQuery implements IDelete, IDeleteResult {

	/**
	 * @inheritdoc
	 */
	public function count() {
		// TODO: Implement count() method.
	}

	/**
	 * @inheritdoc
	 */
	public function lowPriority() {
		return $this->priority(self::PRIORITY_LOW);
	}

	/**
	 * @inheritdoc
	 */
	public function ignoreErrors() {
		$this->onError = self::ON_ERROR_IGNORE;

		return $this;
	}

	public function priority($scenario) {
		if (!in_array($scenario, [self::PRIORITY_LOW])) {
			throw new ErrorException('Wrong value of priority. Please use DeleteQuery::PRIORITY_* constants');
		}

		$this->priority = $scenario;

		return $this;
	}

	protected function prepareSql() {
		$this->query = Formatter::removeLeftTabs($this->query);

		return [
			[
				'bindsString' => $this->bindsString,
				'query' => $this->query,
			],
		];
	}
}