<?php

namespace vendor\ninazu\framework\Component\Db\Interfaces;

interface IQueryPrepare {

	/**
	 * @see IQuery
	 *
	 * @return IQueryResult
	 */
	public function execute();
}