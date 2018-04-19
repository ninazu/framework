<?php

namespace vendor\ninazu\framework\Component\Db\Interfaces;

interface ISelectPrepare {

	/**
	 * @see ISelect
	 *
	 * @return ISelectResult
	 */
	public function execute();
}