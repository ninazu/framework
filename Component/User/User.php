<?php

namespace vendor\ninazu\framework\Component\User;

use vendor\ninazu\framework\Core\Component;

class User extends Component implements IUser {

	const ROLE_USER = 1;

	const ROLE_BANNED = 2;

	public function getRole() {
		//return self::ROLE_BANNED;
		//return self::ROLE_USER;
		//return self::ROLE_AUTHORIZED;
	}
}