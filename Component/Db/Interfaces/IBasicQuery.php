<?php

namespace vendor\ninazu\framework\Component\Db\Interfaces;

interface IBasicQuery {

	/**
	 * @param array $binds
	 *
	 * @return $this
	 */
	public function bindsString(array $binds);

	/**
	 * @param array $binds
	 *
	 * @return $this
	 */
	public function bindInteger(array $binds);

	/**
	 * @param string $placeholder
	 * @param array $values
	 *
	 * @return $this
	 */
	public function bindArray($placeholder, array $values);
}