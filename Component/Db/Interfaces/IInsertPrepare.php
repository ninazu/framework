<?php

namespace vendor\ninazu\framework\Component\Db\Interfaces;

interface IInsertPrepare {

	/**
	 * @see IInsert
	 *
	 * @return IInsertResult
	 */
	public function execute();
}