<?php

namespace vendor\ninazu\framework\Component\Db\Interfaces;

interface ITransaction extends IBasicConnection {

	/**
	 *  Commits a transaction.
	 */
	public function commit();

	/**
	 * Rolls back a transaction.
	 */
	public function rollback();
}