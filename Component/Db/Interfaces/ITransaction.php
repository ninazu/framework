<?php

namespace vendor\ninazu\framework\Component\Db\Interfaces;

interface ITransaction extends IBasicConnection {

	/**
	 *  Commits a transaction.
	 */
	public function commit();

	/**
	 * Rolls back a transaction.
	 *
	 * @param null|string $message
	 */
	public function rollback($message = null);

	/**
	 * @return string
	 */
	public function getRollBackMessage();
}