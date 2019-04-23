<?php

namespace vendor\ninazu\framework\Core\Handler;

use Exception;
use http\Exception\RuntimeException;

class AutoLoader {

	/**
	 * @var array
	 */
	private static $prefixLengthsPsr4;

	/**
	 * @var array
	 */
	private static $prefixDirsPsr4;

	/**
	 * Add path to autoLoader
	 *
	 * @param string $namespace The prefix/namespace, with trailing '\\'
	 * @param array|string $paths $paths The PSR-4 base directories
	 *
	 
	 */
	public static function addSources($namespace, $paths) {
		$length = strlen($namespace);

		if ('\\' !== $namespace[$length - 1]) {
			throw new Exception("A non-empty PSR-4 prefix must end with a namespace separator.");
		}

		self::$prefixLengthsPsr4[$namespace[0]][$namespace] = $length;
		self::$prefixDirsPsr4[$namespace] = (array)$paths;
	}

	/**
	 * AutoLoader callback
	 *
	 * @param string $class
	 *
	 * @return bool
	 */
	public static function loadClass($class) {
		if (!$file = self::findFile($class)) {
			return false;
		}

		include_once $file . '';

		return true;
	}

	/**
	 * Find file for autoLoader
	 *
	 * @param string $class
	 *
	 * @return bool|string
	 */
	private static function findFile($class) {
		if ('\\' == $class[0]) {
			$class = substr($class, 1);
		}

		$first = $class[0];

		if (!isset(self::$prefixLengthsPsr4[$first])) {
			return false;
		}

		$logicalPathPsr4 = strtr($class, '\\', DIRECTORY_SEPARATOR) . '.php';

		foreach (self::$prefixLengthsPsr4[$first] as $prefix => $length) {
			if (0 === strpos($class, $prefix)) {
				foreach (self::$prefixDirsPsr4[$prefix] as $dir) {
					if (file_exists($file = $dir . DIRECTORY_SEPARATOR . substr($logicalPathPsr4, $length))) {
						return $file;
					}
				}
			}
		}

		return false;
	}
}