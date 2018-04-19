<?php

namespace vendor\ninazu\framework\Component\Db\Interfaces;

interface IReplaceResult {

	/**
	 * @return int Amount of rows affected
	 */
	public function count();
}