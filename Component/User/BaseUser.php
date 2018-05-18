<?php

namespace vendor\ninazu\framework\Component\User;

use ErrorException;
use vendor\ninazu\framework\Core\BaseComponent;
use vendor\ninazu\framework\Helper\Reflector;

abstract class BaseUser extends BaseComponent implements IUser {

	const STATUS_ENABLED = 1;

	const STATUS_DISABLED = 2;

	const STATUS_BANNED = 3;

	private $role;

	public function getRole() {
		return $this->role;
	}

	public function setRole($role) {
		$extendedClass = static::class;
		$roles = Reflector::getConstantGroup($extendedClass, 'ROLE_')->getData();

		if (!array_key_exists($role, $roles)) {
			throw new ErrorException("Invalid role. Use {$extendedClass}::ROLE_*");
		}

		$this->role = $role;
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