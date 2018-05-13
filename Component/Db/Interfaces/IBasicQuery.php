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
	public function bindsInteger(array $binds);

	/**
	 * @param string $placeholder
	 * @param array $values
	 *
	 * @return $this
	 */
	public function bindsArray($placeholder, array $values);
}