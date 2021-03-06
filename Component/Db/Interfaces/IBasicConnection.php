<?php

namespace vendor\ninazu\framework\Component\Db\Interfaces;

use vendor\ninazu\framework\Component\Db\Expression;

interface IBasicConnection {

	/**
	 * @return $this
	 */
	public function debugEnable();

	/**
	 * @return $this
	 */
	public function debugDisable();

	/**
	 * @param $attribute
	 * @param $value
	 * @return $this
	 */
	public function setAttribute($attribute, $value);

	/**
	 * @param string $query
	 *
	 * @return ISelect
	 */
	public function select($query);

	/**
	 * @param string $table
	 * @param array|Expression $values
	 *
	 * @return IInsert
	 */
	public function insert($table, $values);

	/**
	 * @param string $table
	 * @param array $values
	 *
	 * @return IUpdate
	 */
	public function update($table, array $values);

	/**
	 * @param string $table
	 * @param string $where
	 *
	 * @return IDelete
	 */
	public function delete($table, $where);

	/**
	 * @param string $table
	 *
	 * @return IReplace
	 */
	public function replace($table);

	/**
	 * @param string $query
	 * @return IQuery
	 */
	public function query($query);
}