<?php

namespace vendor\ninazu\framework\Component\Db\SQLParser;

use Exception;
use http\Exception\RuntimeException;

class Lexer {

	public static function parse($sql) {
		if (!is_string($sql)) {
			throw new RuntimeException($sql);
		}

		$tokens = [];
		$token = "";
		$len = strlen($sql);
		$pos = 0;

		$splitters = [
			"<=>",
			"\r\n",
			"!=",
			">=",
			"<=",
			"<>",
			"<<",
			">>",
			":=",
			"\\",
			"&&",
			"||",
			":=",
			"/*",
			"*/",
			"--",
			">",
			"<",
			"|",
			"=",
			"^",
			"(",
			")",
			"\t",
			"\n",
			"'",
			"\"",
			"`",
			",",
			"@",
			" ",
			"+",
			"-",
			"*",
			"/",
			";",
		];
		$tokenSize = strlen($splitters[0]);
		$hashSet = array_flip($splitters);

		while ($pos < $len) {
			for ($i = $tokenSize; $i > 0; $i--) {
				$subStr = substr($sql, $pos, $i);

				if (isset($hashSet[$subStr])) {

					if ($token !== "") {
						$tokens[] = $token;
					}

					$tokens[] = $subStr;
					$pos += $i;
					$token = "";

					continue 2;
				}
			}

			$token .= $sql[$pos];
			$pos++;
		}

		if ($token !== "") {
			$tokens[] = $token;
		}

		$tokens = self::concatEscapeSequences($tokens);
		$tokens = self::balanceBackTicks($tokens);
		$tokens = self::concatColReferences($tokens);
		$tokens = self::balanceParenthesis($tokens);
		$tokens = self::concatComments($tokens);
		$tokens = self::concatUserDefinedVariables($tokens);

		return $tokens;
	}

	private static function concatUserDefinedVariables($tokens) {
		$i = 0;
		$cnt = count($tokens);
		$userDefined = false;

		while ($i < $cnt) {
			if (!isset($tokens[$i])) {
				$i++;

				continue;
			}

			$token = $tokens[$i];

			if ($userDefined !== false) {
				$tokens[$userDefined] .= $token;
				unset($tokens[$i]);

				if ($token !== "@") {
					$userDefined = false;
				}
			}

			if ($userDefined === false && $token === "@") {
				$userDefined = $i;
			}

			$i++;
		}

		return array_values($tokens);
	}

	private static function concatComments($tokens) {
		$i = 0;
		$cnt = count($tokens);
		$comment = false;
		$inline = false;

		while ($i < $cnt) {
			if (!isset($tokens[$i])) {
				$i++;

				continue;
			}

			$token = $tokens[$i];

			if ($comment !== false) {
				if ($inline === true && ($token === "\n" || $token === "\r\n")) {
					$comment = false;
				} else {
					unset($tokens[$i]);
					$tokens[$comment] .= $token;
				}

				if ($inline === false && ($token === "*/")) {
					$comment = false;
				}
			}

			if (($comment === false) && ($token === "--")) {
				$comment = $i;
				$inline = true;
			}

			if (($comment === false) && ($token === "/*")) {
				$comment = $i;
				$inline = false;
			}

			$i++;
		}

		return array_values($tokens);
	}

	private static function balanceParenthesis($tokens) {
		$token_count = count($tokens);
		$i = 0;

		while ($i < $token_count) {
			if ($tokens[$i] !== '(') {
				$i++;
				continue;
			}

			$count = 1;

			for ($n = $i + 1; $n < $token_count; $n++) {
				$token = $tokens[$n];

				if ($token === '(') {
					$count++;
				}

				if ($token === ')') {
					$count--;
				}

				$tokens[$i] .= $token;
				unset($tokens[$n]);

				if ($count === 0) {
					$n++;
					break;
				}
			}
			$i = $n;
		}

		return array_values($tokens);
	}

	private static function concatColReferences($tokens) {
		$cnt = count($tokens);
		$i = 0;

		while ($i < $cnt) {
			if (!isset($tokens[$i])) {
				$i++;

				continue;
			}

			if ($tokens[$i][0] === ".") {
				$k = $i - 1;
				$len = strlen($tokens[$i]);

				while (($k >= 0) && ($len == strlen($tokens[$i]))) {
					if (!isset($tokens[$k])) {
						$k--;

						continue;
					}

					$tokens[$i] = $tokens[$k] . $tokens[$i];
					unset($tokens[$k]);
					$k--;
				}
			}

			if (self::endsWith($tokens[$i], '.') && !is_numeric($tokens[$i])) {
				$k = $i + 1;
				$len = strlen($tokens[$i]);

				while (($k < $cnt) && ($len == strlen($tokens[$i]))) {
					if (!isset($tokens[$k])) {
						$k++;

						continue;
					}

					$tokens[$i] .= $tokens[$k];
					unset($tokens[$k]);
					$k++;
				}
			}

			$i++;
		}

		return array_values($tokens);
	}

	private static function balanceBackTicks($tokens) {
		$i = 0;
		$cnt = count($tokens);

		while ($i < $cnt) {
			if (!isset($tokens[$i])) {
				$i++;
				continue;
			}

			$token = $tokens[$i];

			if (self::isBackTick($token)) {
				$tokens = self::balanceCharacter($tokens, $i, $token);
			}

			$i++;
		}

		return $tokens;
	}

	private static function concatEscapeSequences($tokens) {
		$tokenCount = count($tokens);
		$i = 0;

		while ($i < $tokenCount) {
			if (self::endsWith($tokens[$i], "\\")) {
				$i++;

				if (isset($tokens[$i])) {
					$tokens[$i - 1] .= $tokens[$i];
					unset($tokens[$i]);
				}
			}

			$i++;
		}

		return array_values($tokens);
	}

	private static function balanceCharacter($tokens, $idx, $char) {
		$token_count = count($tokens);
		$i = $idx + 1;

		while ($i < $token_count) {
			if (!isset($tokens[$i])) {
				$i++;

				continue;
			}

			$token = $tokens[$i];
			$tokens[$idx] .= $token;
			unset($tokens[$i]);

			if ($token === $char) {
				break;
			}

			$i++;
		}

		return array_values($tokens);
	}

	private static function endsWith($haystack, $needle) {
		$length = strlen($needle);

		if ($length == 0) {
			return true;
		}

		return (substr($haystack, -$length) === $needle);
	}

	private static function isBackTick($token) {
		return ($token === "'" || $token === "\"" || $token === "`");
	}
}