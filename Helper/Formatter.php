<?php

namespace vendor\ninazu\framework\Helper;

class Formatter {

	public static function removeLeftTabs($text, $delimiter = "\n") {
		$lines = explode($delimiter, $text);
		$counts = [];
		$min1 = strlen($text);
		$min2 = $min1;

		foreach ($lines as $index => $line) {
			$count = 0;

			if (preg_match('/^\t+/', $line, $matches)) {
				$count = substr_count($matches[0], "\t");
			}

			if ($count <= $min1) {
				$min1 = $count;
			} elseif ($count <= $min2) {
				$min2 = $count;
			}

			$counts[$index] = $count;
		}

		if ($min1 != 0 && $min2 != 1) {
			foreach ($lines as $index => $line) {
				$count = $min2;

				if ($counts[$index] == $min1) {
					$count = $min1;
				}

				$lines[$index] = preg_replace("/^\\t{{$count}}/", '', $line);
			}
		}

		$text = implode($delimiter, $lines);

		return $text;
	}

	public static function addLeftTabs($text, $count, $delimiter = "\n") {
		$lines = explode($delimiter, $text);

		foreach ($lines as $index => $line) {
			$lines[$index] = str_repeat("\t", $count) . $line;
		}

		$text = implode($delimiter, $lines);

		return $text;
	}

	public static function maskText($text, $mask, $position) {
		$textLen = strlen($text);
		$maskLen = strlen($mask);

		if ($position >= $textLen || $position < 0) {
			return $text;
		}

		if ($textLen < ($position + $maskLen)) {
			$mask = substr($mask, 0, $textLen - $position);
		}

		return substr_replace($text, $mask, $position, $maskLen);
	}

	public static function explodeWords($text, $separator = " \t\n;,/|\\<>#") {
		$words = [];
		$tok = strtok($text, $separator);

		while ($tok) {
			$words[] = $tok;
			$tok = strtok($separator);
		}

		return $words;
	}
	
	public static function camelCaseToDash($string) {
		$words = preg_split('/(^[^A-Z]+|[A-Z][^A-Z]+)/', $string, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
		$words = array_map('strtolower', $words);

		return implode('-', $words);
	}

	public static function dashToCamelCase($string) {
		return str_replace('-', '', ucwords($string, '-'));
	}
}
