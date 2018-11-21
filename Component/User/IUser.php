<?php

namespace vendor\ninazu\framework\Component\User;

interface IUser {

	const ROLE_AUTHORIZED = '@';

	const ROLE_GUEST = '?';

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