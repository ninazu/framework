<?php

namespace vendor\ninazu\framework\Component\Db\Interfaces;

interface IDelete extends IBasicQuery {

	const PRIORITY_LOW = ' LOW_PRIORITY';

	const ON_ERROR_IGNORE = ' IGNORE';

	/**
	 * @return $this
	 */
	public function lowPriority();

	/**
	 * @return $this
	 */
	public function ignoreErrors();
}