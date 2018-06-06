<?php

namespace vendor\ninazu\framework\Component\User;

interface IUserIdentity {

	public function getRole();

	/**
	 * @param int $role
	 *
	 * @return $this
	 */
	public function setRole($role);

	public function getId();

	/**
	 * @param int $id
	 *
	 * @return $this
	 */
	public function setId($id);
}