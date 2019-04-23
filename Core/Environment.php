<?php

namespace vendor\ninazu\framework\Core;

use Exception;
use http\Exception\RuntimeException;

class Environment {

	const ENVIRONMENT_PRODUCTION = 'prod';

	const ENVIRONMENT_STAGING = 'test';

	const ENVIRONMENT_DEVELOPMENT = 'dev';

	const ENVIRONMENT_LOCAL = 'local';

	private static $environment;

	private static $register = [];

	private static $hostname;

	private static $developmentIPs;

	private static $IP;

	private static $isCLI;

	private static $isDevelopmentIP;

	/**
	 * Safe getter with dot notation
	 *
	 * @param string $name
	 * @param mixed $default
	 *
	 * @return array|mixed|string
	 */
	public static function get($name, $default = '') {
		$keys = explode('.', $name);

		$pointer = self::$register;

		foreach ($keys as $key) {
			if (!isset($pointer[$key])) {
				return $default;
			}

			$pointer = &$pointer[$key];
		}

		return $pointer;
	}

	/**
	 * Safe setter with dot notation
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	public static function set($name, $value) {
		if (is_null($name)) {
			self::$register = array_replace_recursive(self::$register, $value);
		}

		$arr = &self::$register;
		$keys = explode('.', $name);

		foreach ($keys as $key) {
			$arr = &$arr[$key];
		}

		$arr = $value;
	}

	/**
	 * Return current environment
	 *
	 * @return string
	 *
	 
	 */
	public static function getEnvironment() {
		self::checkInitialize();

		return self::$environment;
	}

	/**
	 * Set current environment
	 *
	 * @param $value
	 *
	 
	 */
	public static function setEnvironment($value) {
		switch ($value) {
			case self::ENVIRONMENT_DEVELOPMENT:
			case self::ENVIRONMENT_PRODUCTION:
			case self::ENVIRONMENT_STAGING:
			case self::ENVIRONMENT_LOCAL:
				break;
			default:
				throw new RuntimeException('Wrong environment allowed Environment::ENVIRONMENT_* const');
		}

		self::$environment = $value;
	}

	/**
	 * @return bool
	 */
	public static function isInitialized() {
		return isset(self::$environment);
	}

	/**
	 * @return bool
	 *
	 
	 */
	public static function isProduction() {
		self::checkInitialize();

		return self::ENVIRONMENT_PRODUCTION == self::$environment;
	}

	/**
	 * @return bool
	 *
	 
	 */
	public static function isLocal() {
		self::checkInitialize();

		return self::ENVIRONMENT_LOCAL == self::$environment;
	}

	/**
	 * @return bool
	 *
	 
	 */
	public static function isDevelopment() {
		self::checkInitialize();

		return self::ENVIRONMENT_DEVELOPMENT == self::$environment;
	}

	/**
	 * @return bool
	 *
	 
	 */
	public static function isStaging() {
		self::checkInitialize();

		return self::ENVIRONMENT_STAGING == self::$environment;
	}

	/**
	 * @return bool
	 */
	public static function isDevelopmentIP() {
		if (is_null(self::$isDevelopmentIP)) {
			self::$isDevelopmentIP = in_array(self::getIP(), self::$developmentIPs);
		}

		return self::$isDevelopmentIP;
	}

	/**
	 * @return string
	 */
	public static function getIP() {
		if (!self::$IP) {
			if (self::isCLI()) {
				$IPAddress = gethostbyname(self::getHostname());
			} else {
				$IPAddress = $_SERVER['REMOTE_ADDR'];

				if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {
					$IPAddress = array_pop(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']));
				}
			}

			self::$IP = $IPAddress;
		}

		return self::$IP;
	}

	/**
	 * Detect web application
	 *
	 * @return bool
	 */
	public static function isCLI() {
		if (empty(self::$isCLI)) {
			if (substr(php_sapi_name(), 0, 3) == 'cli' || !array_key_exists('REQUEST_METHOD', $_SERVER)) {
				self::$isCLI = true;
			} else {
				self::$isCLI = false;
			}
		}

		return self::$isCLI;
	}

	/**
	 * Return hostname for identity environment
	 *
	 * @return string
	 */
	public static function getHostname() {
		if (empty(self::$hostname)) {
			self::$hostname = gethostname();
		}

		return self::$hostname;
	}

	/**
	 * @param string[] $IPs
	 */
	public static function setDevelopmentIP(array $IPs) {
		self::$developmentIPs = $IPs;
		self::$isDevelopmentIP = null;
	}

	/**
	 * @param $path
	 *
	 * @return string
	 */
	public static function getLastPath($path) {
		$parts = explode('/', realpath($path));

		return end($parts);
	}

	/**
	 
	 */
	private static function checkInitialize() {
		if (!self::isInitialized()) {
			throw new RuntimeException('Environment not initialized, call Environment::setEnvironment(Environment::ENVIRONMENT_*)');
		}
	}
}