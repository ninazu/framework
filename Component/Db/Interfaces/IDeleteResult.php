<?php

namespace vendor\ninazu\framework\Component\Db\Interfaces;

interface IDeleteResult {

	/**
	 * @return int Amount of deleted rows
	 */
	public function count();
}