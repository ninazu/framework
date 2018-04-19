<?php

namespace vendor\ninazu\framework\Component\Db\Interfaces;

interface IMysql {

	/**
	 * Create connection without re-creation
	 *
	 * @param string $name key from config
	 *
	 * @return IConnection
	 */
	public function connect($name);

	/**
	 * @param string $name key from config
	 */
	public function disconnect($name);

	/**
	 * RAW expression of MySQL. Injected as is
	 *
	 * @param string $expression
	 *
	 * @return \vendor\ninazu\framework\Component\Db\Expression
	 */
	public static function Expression($expression);
}