<?php

namespace vendor\ninazu\framework\Model;

use ErrorException;
use vendor\ninazu\framework\Component\Db\Interfaces\IConnection;

abstract class BaseModel {

	/**@var IConnection $connection */
	protected $connection;

	public function setConnection(IConnection $connection) {
		$this->connection = $connection;
	}

	abstract public function save();

	protected static function tableName() {
		$class = static::class;

		if (!preg_match('/\\(.*?)Model$/', $class)) {
			throw new ErrorException("Override 'tableName' or rename model with standard '{$class}'");
		}
	}
}