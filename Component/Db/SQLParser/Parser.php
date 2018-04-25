<?php

namespace vendor\ninazu\framework\Component\Db\SQLParser;

class Parser {

	const T_NOT = 1;

	const T_AND = 2;

	const T_OR = 3;

	const T_QUESTION = 4;

	const T_COLON = 5;

	const T_EQ = 6;

	const T_LIKE = 7;

	const T_NE = 8;

	const T_GT = 9;

	const T_GE = 10;

	const T_LT = 11;

	const T_LE = 12;

	const T_IN = 13;

	const T_PLUS = 14;

	const T_MINUS = 15;

	const T_CONCAT = 16;

	const T_TIMES = 17;

	const T_DIV = 18;

	const T_MOD = 19;

	const T_PIPE = 20;

	const T_BITWISE = 21;

	const T_FILTER_PIPE = 22;

	const SEMICOLON = 23;

	const PAR_OPEN = 24;

	const PAR_CLOSE = 25;

	const BEGIN = 26;

	const SAVEPOINT = 27;

	const RELEASE = 28;

	const ROLLBACK = 29;

	const TO = 30;

	const TRANSACTION = 31;

	const WORK = 32;

	const COMMIT = 33;

	const T_END = 34;

	const ALTER = 35;

	const TABLE = 36;

	const DROP = 37;

	const PRIMARY = 38;

	const KEY = 39;

	const INDEX = 40;

	const SET = 41;

	const T_DEFAULT = 42;

	const MODIFY = 43;

	const ADD = 44;

	const RENAME = 45;

	const CREATE = 46;

	const ON = 47;

	const UNIQUE = 48;

	const T_AS = 49;

	const CHANGE = 50;

	const T_COLUMN = 51;

	const T_FIRST = 52;

	const T_AFTER = 53;

	const SELECT = 54;

	const ALL = 55;

	const DISTINCT = 56;

	const DISTINCTROW = 57;

	const HIGH_PRIORITY = 58;

	const STRAIGHT_JOIN = 59;

	const SQL_SMALL_RESULT = 60;

	const SQL_BIG_RESULT = 61;

	const SQL_CACHE = 62;

	const SQL_CALC_FOUND_ROWS = 63;

	const SQL_BUFFER_RESULT = 64;

	const SQL_NO_CACHE = 65;

	const FROM = 66;

	const COMMA = 67;

	const JOIN = 68;

	const INNER = 69;

	const LEFT = 70;

	const RIGHT = 71;

	const NATURAL = 72;

	const OUTER = 73;

	const USING = 74;

	const WHERE = 75;

	const ORDER = 76;

	const BY = 77;

	const DESC = 78;

	const ASC = 79;

	const LIMIT = 80;

	const OFFSET = 81;

	const GROUP = 82;

	const HAVING = 83;

	const VALUES = 84;

	const DELETE = 85;

	const UPDATE = 86;

	const INSERT = 87;

	const REPLACE = 88;

	const INTO = 89;

	const DUPLICATE = 90;

	const VIEW = 91;

	const NUMBER = 92;

	const T_UNSIGNED = 93;

	const COLLATE = 94;

	const T_NULL = 95;

	const AUTO_INCREMENT = 96;

	const T_IS = 97;

	const T_CASE = 98;

	const T_ELSE = 99;

	const WHEN = 100;

	const THEN = 101;

	const INTERVAL = 102;

	const ALPHA = 103;

	const T_STRING1 = 104;

	const T_STRING2 = 105;

	const T_DOT = 106;

	const COLUMN = 107;

	const QUESTION = 108;

	const T_DOLLAR = 109;
}