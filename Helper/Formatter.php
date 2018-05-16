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
}