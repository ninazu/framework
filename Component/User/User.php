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

	private static function generateSalt() {
		return md5(openssl_random_pseudo_bytes(16));
	}

	public static function generateToken() {
		return hash('sha256', (openssl_random_pseudo_bytes(128)));
	}

	public static function calculateHash($password, $salt) {
		return hash('sha256', $password . $salt);
	}
}