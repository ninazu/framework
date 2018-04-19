<?php

namespace vendor\ninazu\framework\Component\Db\Interfaces;

interface IUpdatePrepare {

	/**
	 * @see IUpdate
	 *
	 * @return IUpdateResult
	 */
	public function execute();
}