<?php

namespace vendor\ninazu\framework\Component\Db\Interfaces;

interface IReplacePrepare {

	/**
	 * @see IReplace
	 *
	 * @return IReplaceResult
	 */
	public function execute();
}