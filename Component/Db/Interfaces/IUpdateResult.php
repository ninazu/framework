<?php

namespace vendor\ninazu\framework\Component\Db\Interfaces;

interface IUpdateResult {

	/**
	 * @return int Amount of updated rows
	 */
	public function affectedRows();
}