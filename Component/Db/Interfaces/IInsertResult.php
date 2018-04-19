<?php

namespace vendor\ninazu\framework\Component\Db\Interfaces;

interface IInsertResult {

	/**
	 * @return int
	 */
	public function affectedRows();

	/**
	 * @return int
	 */
	public function lastInsertedId();
}