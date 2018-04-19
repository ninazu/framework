<?php

namespace vendor\ninazu\framework\Component\Db\Interfaces;

interface IConnection extends IBasicConnection {

	/**
	 * Initiates a transaction.
	 *
	 * @return ITransaction
	 */
	public function beginTransaction();
}