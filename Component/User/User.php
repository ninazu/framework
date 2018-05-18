<?php

namespace vendor\ninazu\framework\Component\User;

use vendor\ninazu\framework\Core\Component;

abstract class User extends Component implements IUser {

	const ROLE_USER = 1;

	const STATUS_ENABLED = 1;

	const STATUS_DISABLED = 2;

	const STATUS_BANNED = 3;

	public function getRole() {
		//return self::ROLE_USER;
		//return self::ROLE_AUTHORIZED;
	}

	private static function generateSalt() {
		return md5(openssl_random_pseudo_bytes(16));
	}

	public static function generateToken($TTL) {
		$randomToken = hash('sha256', (openssl_random_pseudo_bytes(128)));

		return (time() + $TTL) . '-' . $randomToken;
	}

	public static function calculateHash($password, $salt) {
		return hash('sha256', $password . $salt);
	}

	public static function isValidToken($token) {
		if (empty($token)) {
			return false;
		}

		list($time) = explode('-', $token);

		if (time() > $time) {
			return false;
		}

		return is_numeric($time) && (int)$time == $time;
	}
}