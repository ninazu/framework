<?php

namespace vendor\ninazu\framework\Component\Db\SQLParser;

class Processor {

	private $tokens;

	public function __construct(array $tokens) {
		$this->tokens = $tokens;
	}

	public function getPlaceholders() {
		$placeholders = [];
		$position = 0;

		foreach ($this->tokens as $i => $token) {
			$recursiveOffsetLeft = 0;
			$recursiveOffsetRight = 0;

			if ($token === '?' || $token[0] === ':') {
				$placeholders[$token][] = [
					'pos' => $position,
					'offset' => 0,
				];
			} else if ($token[0] === '(' && substr($token, -1) === ')') {
				$tokenAfter = $token;
				$offset = [
					'left' => 0,
					'right' => 0,
				];

				do {
					$token = $tokenAfter;
					$tokenAfter = self::removeParenthesis($token, $offset);
					$recursiveOffsetLeft = $offset['left'];
					$recursiveOffsetRight = $offset['right'];
				} while ($token != $tokenAfter);

				$subQuery = Lexer::parse($token);

				if (count($subQuery) == 1) {
					$placeholders[$token][] = [
						'pos' => $position + $recursiveOffsetLeft,
						'offset' => $recursiveOffsetRight,
					];
				} else {
					$tmp = (new self($subQuery))->getPlaceholders();

					foreach ($tmp as $subQueryToken => $subQueryPositions) {
						foreach ($subQueryPositions as $subQueryPosition) {
							$placeholders[$subQueryToken][] = [
								'pos' => $position + $recursiveOffsetLeft + $subQueryPosition['pos'],
								'offset' => $recursiveOffsetRight + $subQueryPosition['offset'],
							];
						}
					}
				}
			}

			$position += strlen($token) + $recursiveOffsetRight + $recursiveOffsetLeft;
		}

		return $placeholders;
	}

	/**
	 * @param string $str
	 * @param array $result
	 */
	private static function trimWithCalc(&$str, &$result) {
		$before = strlen($str);
		$str = ltrim($str);
		$after = strlen($str);
		$result['left'] += $before - $after;

		$before = $after;
		$str = rtrim($str);
		$after = strlen($str);
		$result['right'] += $before - $after;
	}

	private static function removeParenthesis($token, &$offset) {
		$parenthesisRemoved = 0;
		$trim = $token;
		self::trimWithCalc($token, $offset);

		if ($trim !== "" && $trim[0] === "(") {
			$parenthesisRemoved++;
			$trim[0] = " ";
			self::trimWithCalc($trim, $offset);
		}

		$parenthesis = $parenthesisRemoved;
		$i = 0;
		$string = 0;

		while ($i < strlen($trim)) {

			if ($trim[$i] === "\\") {
				$i += 2;
				continue;
			}

			if ($trim[$i] === "'" || $trim[$i] === '"') {
				$string++;
			}

			if (($string % 2 === 0) && ($trim[$i] === "(")) {
				$parenthesis++;
			}

			if (($string % 2 === 0) && ($trim[$i] === ")")) {
				if ($parenthesis == $parenthesisRemoved) {
					$trim[$i] = " ";
					$parenthesisRemoved--;
				}

				$parenthesis--;
			}

			$i++;
		}

		self::trimWithCalc($trim, $offset);

		return $trim;
	}
}