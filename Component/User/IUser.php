<?php

namespace vendor\ninazu\framework\Component\User;

interface IUser {

	const ROLE_AUTHORIZED = '@';

	const ROLE_GUEST = '?';

	public function getIdentity();

	public function setIdentity(IUserIdentity $user);

}