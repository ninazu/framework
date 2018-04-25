<?php

namespace vendor\ninazu\framework\Component\Db\SQLParser;

use Exception;

class Tokenizer {

	const INITIAL = 1;

	const IN_STRING_1 = 2;

	const IN_STRING_2 = 3;

	const IN_STRING_3 = 4;

	const IN_ESCAPE_1 = 5;

	const IN_ESCAPE_2 = 6;

	const IN_ESCAPE_3 = 7;

	private $data;

	private $step = self::INITIAL;

	private $pos = 0;

	private $line = 0;

	private $token;

	private $value;

	private $string;

	/**
	 * Parser constructor.
	 *
	 * @param string $sql
	 */
	private function __construct($sql) {
		$this->data = trim($sql);
	}

	public static function parse($sql) {
		$instance = new self($sql);
		$stmt = 0;
		$tokens = [];

		while ($instance->process()) {
			switch ($instance->token) {
				case "comment";
					$comments[$stmt][] = trim(trim($instance->value, "-/*"));
					continue 2;

				case Parser::SEMICOLON:
					++$stmt;
					break;
			}

			$tokens[] = [
				$instance->token => $instance->value,
			];
		}

		return $instance;
	}

	/**
	 * @return bool
	 * @throws Exception
	 */
	private function process() {
		$stepName = "step{$this->step}";

		if (!method_exists($this, $stepName)) {
			throw new Exception('Step not implemented yet');
		}

		return $this->$stepName();
	}

	/**
	 * @uses Tokenizer::step7
	 */
	private function step7() {
		return $this->step2u(null, self::IN_STRING_3, [
			'/\G./i ',
		]);
	}

	/**
	 * @uses Tokenizer::step6
	 */
	private function step6() {
		return $this->step2u(null, self::IN_STRING_2, [
			'/\G./i ',
		]);
	}

	/**
	 * @uses Tokenizer::step5
	 */
	private function step5() {
		return $this->step2u(null, self::IN_STRING_1, [
			'/\G./i ',
		]);
	}

	/**
	 * @uses Tokenizer::step4
	 */
	private function step4() {
		return $this->step2u(Parser::COLUMN, self::IN_ESCAPE_3, [
			'/\G`/i ',
			'/\G\\\\/i ',
			'/\G[^`\\\\]+/i ',
		]);
	}

	/**
	 * @uses Tokenizer::step3
	 */
	private function step3() {
		return $this->step2u(Parser::T_STRING2, self::IN_ESCAPE_2, [
			'/\G\'/i ',
			'/\G\\\\/i ',
			'/\G[^\'\\\\]+/i ',
		]);
	}

	/**
	 * @uses Tokenizer::step2
	 */
	private function step2() {
		return $this->step2u(Parser::T_STRING1, self::IN_ESCAPE_1, [
			'/\G\"/i ',
			'/\G\\\\/i ',
			'/\G[^\"\\\\]+/i ',
		]);
	}

	private function step2u($token, $step, $rules) {
		if ($this->pos >= strlen($this->data)) {
			return false;
		}

		do {
			$match = false;

			foreach ($rules as $index => $rule) {
				if (preg_match($rule, substr($this->data, $this->pos), $matches)) {
					if ($match) {
						if (strlen($matches[0]) > strlen($match[0][0])) {
							$match = [$matches, $index];
						}
					} else {
						$match = [$matches, $index];
					}
				}
			}

			if (!$match) {
				throw new Exception("Unexpected input {$this->line}:{$this->data[$this->pos]}");
			}

			$this->token = $match[1];
			$this->value = $match[0][0];

			if (is_null($token)) {
				if (is_null($step)) {
					$r = $this->token1();
				} else {
					$r = $this->token3u($step);
				}
			} else {
				$r = $this->token2u($token, $step);
			}

			if ($r === null) {
				$this->pos += strlen($this->value);
				$this->line += substr_count($this->value, "\n");

				return true;
			} elseif ($r === true) {
				return $this->process();
			} elseif ($r === false) {
				$this->pos += strlen($this->value);
				$this->line += substr_count($this->value, "\n");

				if ($this->pos >= strlen($this->data)) {
					return false;
				}

				continue;
			} else {
				$morePatterns = array_slice($rules, $this->token, true);

				do {
					if (!isset($morePatterns[$this->token])) {
						throw new Exception('This is last token');
					}

					$match = false;

					foreach ($morePatterns[$this->token] as $index => $rule) {
						if (preg_match('/' . $rule . '/i',
							$this->data, $matches, null, $this->pos)) {
							$matches = array_filter($matches, 'strlen');

							if ($match) {
								if (strlen($matches[0]) > strlen($match[0][0])) {
									$match = [$matches, $index];
								}
							} else {
								$match = [$matches, $index];
							}
						}
					}

					if (!$match) {
						throw new Exception("Unexpected input {$this->line}:{$this->data[$this->pos]}");
					}

					$this->token = $match[1];
					$this->value = $match[0][0];
					$this->line = substr_count($this->value, "\n");

					if (is_null($token)) {
						if (is_null($step)) {
							$r = $this->token1();
						} else {
							$r = $this->token3u($step);
						}
					} else {
						$r = $this->token2u($token, $step);
					}
				} while ($r !== null || !$r);

				if ($r === true) {
					return $this->process();
				} else {
					$this->pos += strlen($this->value);
					$this->line += substr_count($this->value, "\n");

					return true;
				}
			}
		} while (true);
	}

	/**
	 * @uses Tokenizer::step1
	 */
	private function step1() {
		return $this->step2u(null, null, [
			'/\G[ \t\n]+/i ',
			'/\G\"/i ',
			'/\G\'/i ',
			'/\G`/i ',
			'/\G--[^\n]+/i ',
			'/\Gwhen/i ',
			'/\Gunsigned/i ',
			'/\Gcase/i ',
			'/\Gcreate/i ',
			'/\Gthen/i ',
			'/\Gdefault/i ',
			'/\Gelse/i ',
			'/\Gmodify/i ',
			'/\Gautoincrement/i ',
			'/\Gauto_increment/i ',
			'/\Gcollate/i ',
			'/\Gend/i ',
			'/\Gnull/i ',
			'/\Gselect/i ',
			'/\Ggroup/i ',
			'/\Ginsert/i ',
			'/\Gupdate/i ',
			'/\Gdelete/i ',
			'/\Ginto/i ',
			'/\Gleft/i ',
			'/\Gright/i ',
			'/\Ginner/i ',
			'/\Gjoin/i ',
			'/\Gfrom/i ',
			'/\Glimit/i ',
			'/\Gdelete/i ',
			'/\Goffset/i ',
			'/\Gvalues/i ',
			'/\Gset/i ',
			'/\Gdrop/i ',
			'/\Gtable/i ',
			'/\Gnot/i ',
			'/\G>=/i ',
			'/\G<=/i ',
			'/\G%/i ',
			'/\G\//i ',
			'/\G>/i ',
			'/\G</i ',
			'/\G\\(/i ',
			'/\G\\)/i ',
			'/\G;/i ',
			'/\G\\*/i ',
			'/\G\\+/i ',
			'/\G-/i ',
			'/\G=/i ',
			'/\G\\?/i ',
			'/\G\\$/i ',
			'/\G:/i ',
			'/\G\\./i ',
			'/\G,/i ',
			'/\Gon/i ',
			'/\Gduplicate/i ',
			'/\Gin/i ',
			'/\Gall/i ',
			'/\Gdistinct/i ',
			'/\Gnatural/i ',
			'/\Gouter/i ',
			'/\Gusing/i ',
			'/\Ginterval/i ',
			'/\Ghaving/i ',
			'/\Gwhere/i ',
			'/\Gview/i ',
			'/\Glike/i ',
			'/\Gorder/i ',
			'/\Gprimary/i ',
			'/\Gcolumn/i ',
			'/\Gfirst/i ',
			'/\Gafter/i ',
			'/\Gchange/i ',
			'/\Gindex/i ',
			'/\Gadd/i ',
			'/\Galter/i ',
			'/\Gunique/i ',
			'/\Gkey/i ',
			'/\Gdesc/i ',
			'/\Gasc/i ',
			'/\Gby/i ',
			'/\Gand/i ',
			'/\Gor/i ',
			'/\Gis/i ',
			'/\G\\|\\|/i ',
			'/\G!=/i ',
			'/\Gbegin/i ',
			'/\Gwork/i ',
			'/\Gtransaction/i ',
			'/\Gcommit/i ',
			'/\Grollback/i ',
			'/\Gsavepoint/i ',
			'/\Grelease/i ',
			'/\Gto/i ',
			'/\Gas/i ',
			'/\Grename/i ',
			'/\G[0-9]+(\\.[0-9]+)?|0x[0-9a-fA-F]+/i ',
			'/\Gsql_cache/i ',
			'/\Gsql_calc_found_rows/i ',
			'/\Gsql_no_cache/i ',
			'/\Ghigh_priority/i ',
			'/\Gstraight_join/i ',
			'/\Gsql_small_result/i ',
			'/\Gsql_big_result/i ',
			'/\Gsql_buffer_result/i ',
			'/\G[a-z_][a-z0-9_]*/i ',
		]);
	}

	private function token3u($step) {
		switch ($this->token) {
			case 0:
				$this->step = $step;
				$this->string .= $this->value;

				return null;

			default:
				throw new Exception("Token '{$this->token}' not implemented yet");
		}
	}

	private function token2u($token, $step) {
		switch ($this->token) {
			case 0:
				$this->value = $this->string;
				$this->token = $token;
				$this->pos = strlen($this->string) - 1;
				$this->string = '';
				$this->step = self::INITIAL;

				return null;

			case 1:
				$this->step = $step;
				$this->pos++;

				return true;

			case 2:
				$this->string .= $this->value;

				return false;

			default:
				throw new Exception("Token '{$this->token}' not implemented yet");
		}
	}

	private function token1() {
		$tokenMap = [
			4 => 'comment',
			5 => Parser::WHEN,
			6 => Parser::T_UNSIGNED,
			7 => Parser::T_CASE,
			8 => Parser::CREATE,
			9 => Parser::THEN,
			10 => Parser::T_DEFAULT,
			11 => Parser::T_ELSE,
			12 => Parser::MODIFY,
			13 => Parser::AUTO_INCREMENT,
			14 => Parser::AUTO_INCREMENT,
			15 => Parser::COLLATE,
			16 => Parser::T_END,
			17 => Parser::T_NULL,
			18 => Parser::SELECT,
			19 => Parser::GROUP,
			20 => Parser::INSERT,
			21 => Parser::UPDATE,
			22 => Parser::DELETE,
			23 => Parser::INTO,
			24 => Parser::LEFT,
			25 => Parser::RIGHT,
			26 => Parser::INNER,
			27 => Parser::JOIN,
			28 => Parser::FROM,
			29 => Parser::LIMIT,
			30 => Parser::DELETE,
			31 => Parser::OFFSET,
			32 => Parser::VALUES,
			33 => Parser::SET,
			34 => Parser::DROP,
			35 => Parser::TABLE,
			36 => Parser::T_NOT,
			37 => Parser::T_GE,
			38 => Parser::T_LE,
			39 => Parser::T_MOD,
			40 => Parser::T_DIV,
			41 => Parser::T_GT,
			42 => Parser::T_LT,
			43 => Parser::PAR_OPEN,
			44 => Parser::PAR_CLOSE,
			45 => Parser::SEMICOLON,
			46 => Parser::T_TIMES,
			47 => Parser::T_PLUS,
			48 => Parser::T_MINUS,
			49 => Parser::T_EQ,
			50 => Parser::QUESTION,
			51 => Parser::T_DOLLAR,
			52 => Parser::T_COLON,
			53 => Parser::T_DOT,
			54 => Parser::COMMA,
			55 => Parser::ON,
			56 => Parser::DUPLICATE,
			57 => Parser::T_IN,
			58 => Parser::ALL,
			59 => Parser::DISTINCT,
			60 => Parser::NATURAL,
			61 => Parser::OUTER,
			62 => Parser::USING,
			63 => Parser::INTERVAL,
			64 => Parser::HAVING,
			65 => Parser::WHERE,
			66 => Parser::VIEW,
			67 => Parser::T_LIKE,
			68 => Parser::ORDER,
			69 => Parser::PRIMARY,
			70 => Parser::T_COLUMN,
			71 => Parser::T_FIRST,
			72 => Parser::T_AFTER,
			73 => Parser::CHANGE,
			74 => Parser::INDEX,
			75 => Parser::ADD,
			76 => Parser::ALTER,
			77 => Parser::UNIQUE,
			78 => Parser::KEY,
			79 => Parser::DESC,
			80 => Parser::ASC,
			81 => Parser::BY,
			82 => Parser::T_AND,
			83 => Parser::T_OR,
			84 => Parser::T_IS,
			85 => Parser::T_OR,
			86 => Parser::T_NE,
			87 => Parser::BEGIN,
			88 => Parser::WORK,
			89 => Parser::TRANSACTION,
			90 => Parser::COMMIT,
			91 => Parser::ROLLBACK,
			92 => Parser::SAVEPOINT,
			93 => Parser::RELEASE,
			94 => Parser::TO,
			95 => Parser::T_AS,
			96 => Parser::RENAME,
			97 => Parser::NUMBER,
			98 => Parser::SQL_CACHE,
			99 => Parser::SQL_CALC_FOUND_ROWS,
			100 => Parser::SQL_NO_CACHE,
			101 => Parser::HIGH_PRIORITY,
			102 => Parser::STRAIGHT_JOIN,
			103 => Parser::SQL_BIG_RESULT,
			104 => Parser::SQL_BIG_RESULT,
			105 => Parser::SQL_BUFFER_RESULT,
			106 => Parser::ALPHA,
		];

		switch (true) {
			case ($this->token === 0):
				return false;

			case ($this->token >= 1 && $this->token <= 3):
				$this->step = $this->token + 1;
				$this->string = '';
				$this->pos++;

				return true;

			case ($this->token >= 4 && $this->token <= 106):
				$this->token = $tokenMap[$this->token];

				return null;

			default:
				throw new Exception("Token '{$this->token}' not implemented yet");
		}
	}
}