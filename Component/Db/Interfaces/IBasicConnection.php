<?php

namespace vendor\ninazu\framework\Component\Db\Interfaces;

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
	 * @param string $query
	 *
	 * @return ISelect
	 */
	public function select($query);

	/**
	 * @param string $table
	 * @param array $values
	 *
	 * @return IInsert
	 */
	public function insert($table, array $values);

	/**
	 * @param string $table
	 * @param array $values
	 * @param string $where
	 *
	 * @return IUpdate
	 */
	public function update($table, array $values, $where);

	/**
	 * @param string $table
	 *
	 * @return IDelete
	 */
	public function delete($table);

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