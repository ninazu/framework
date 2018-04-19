<?php

namespace vendor\ninazu\framework\Component\Db\Interfaces;

interface IDeletePrepare {

	/**
	 * @see IDelete
	 *
	 * @return IDeleteResult
	 */
	public function execute();
}