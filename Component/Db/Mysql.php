<?php

namespace vendor\ninazu\framework\Component\Db;

use ErrorException;
use vendor\ninazu\framework\Component\Db\Interfaces\IMysql;
use vendor\ninazu\framework\Core\Component;

class Mysql extends Component implements IMysql {

	const FORMAT_DATE = 'Y-m-d';

	const FORMAT_TIME = 'H:i:s';

	const FORMAT_DATETIME = 'Y-m-d H:i:s';

	const MAX_BINDS_COUNT = 64000;

	protected $databases;

	/**
	 * @var Connection[] $connections
	 */
	private $connections = [];

	/**
	 * @inheritdoc
	 */
	public function connect($name) {
		if (!isset($this->connections[$name])) {
			if (!isset($this->databases[$name])) {
				throw new ErrorException("Database '{$name}' not configured");
			}

			$connection = new Connection($name, $this);
			$connection->fillFromConfig($this->databases[$name]);
			$connection->connect();

			$this->connections[$name] = $connection;
		}

		return $this->connections[$name];
	}

	/**
	 * @inheritdoc
	 */
	public function disconnect($name) {
		if (!isset($this->connections[$name])) {
			return false;
		}

		$this->connections[$name]->disconnect();
		unset($this->connections[$name]);

		return true;
	}

	/**
	 * @inheritdoc
	 */
	public static function Expression($expression) {
		return new Expression($expression);
	}
}