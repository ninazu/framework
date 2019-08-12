<?php

namespace vendor\ninazu\framework\Component\User;

interface IUser {

	/**
	 * @return $this;
	 */
	public function getIdentity();

	/**
	 * @param IUserIdentity $user
	 *
	 * @return $this;
	 */
	public function setIdentity(IUserIdentity $user);
}