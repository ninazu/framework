<?php

namespace vendor\ninazu\framework\Component\Db;

use ErrorException;
use PDO;
use ReflectionFunction;
use vendor\ninazu\framework\Component\Db\Interfaces\ISelect;
use vendor\ninazu\framework\Component\Db\Interfaces\ISelectResult;

class SelectQuery extends Query implements ISelect, ISelectResult {

	/**@var string $columnName */
	private $columnName = null;

	/**@var callable $callBack */
	private $callBack = null;

	/**@var int $callBackParamsCount */
	private $callBackParamsCount = 0;

	/**@var Meta $columnMeta */
	private $columnMeta = null;

	/**
	 * @inheritdoc
	 */
	public function indexBy($columnName) {
		$this->columnName = $columnName;

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function handler(callable $callback) {
		$this->callBackParamsCount = (new ReflectionFunction($callback))->getNumberOfParameters();

		if ($this->callBackParamsCount < 1 || $this->callBackParamsCount > 3) {
			throw new ErrorException('Invalid number of params for handler callback. Expected number of params 1-3');
		}

		$this->callBack = $callback;

		if ($this->callBackParamsCount === 3) {
			$columnCount = $this->columnCount();

			for ($i = 0; $i < $columnCount; $i++) {
				$meta = $this->columnMeta($i);

				$columnMeta[$meta['name']] = [
					'type' => $meta['native_type'],
					'precision' => $meta['precision'],
					'flags' => $meta['flags'],
					'smartConvert' => function ($value) use ($meta) {
						switch ($meta['native_type']) {
							case 'TINY':
							case 'SHORT':
							case 'LONG':
							case 'INT24':
							case 'SET':
								return is_null($value) ? null : (int)$value;

							case 'DECIMAL':
							case 'NEWDECIMAL':
							case 'FLOAT':
							case 'DOUBLE':
								return is_null($value) ? null : (double)$value;

							case 'BIT':
								return (bool)$value;

							case 'NULL':
								return null;

							default:
								/**
								 * MYSQL_TYPE_LONGLONG
								 * MYSQL_TYPE_TIMESTAMP
								 * MYSQL_TYPE_DATE
								 * MYSQL_TYPE_TIME
								 * MYSQL_TYPE_DATETIME
								 * MYSQL_TYPE_YEAR
								 * MYSQL_TYPE_STRING
								 * MYSQL_TYPE_VAR_STRING
								 * MYSQL_TYPE_BLOB
								 * MYSQL_TYPE_ENUM    ENUM
								 * MYSQL_TYPE_GEOMETRY
								 */
								return $value;
						}
					},
				];

				$this->columnMeta = new Meta($columnMeta);
			}
		}

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function queryAll() {
		if (is_null($this->statement)) {
			throw new ErrorException('The execute() method must be called before fetchAll()');
		}

		$result = $this->statement->fetchAll(PDO::FETCH_ASSOC);

		if (empty($result)) {
			$this->reset();

			return $result;
		}

		$callBack = $this->callBack;

		if (is_null($this->columnName) && is_null($this->callBack)) {
			$this->reset();

			return $result;
		}

		if (!is_null($this->columnName) && !empty($result) && !isset($result[0][$this->columnName])) {
			throw new ErrorException("Column '{$this->columnName}' does not exist in result set.");
		}

		$keyedResult = [];

		foreach ($result as $index => $row) {
			$index = !is_null($this->columnName) ? $row[$this->columnName] : $index;

			switch ($this->callBackParamsCount) {
				case 0:
					$keyedResult[$index] = $row;
					break;

				case 1:
					$keyedResult[$index] = $callBack($row);
					break;

				case 2:
					$keyedResult[$index] = $callBack($row, isset($keyedResult[$index]) ? $keyedResult[$index] : []);
					break;

				case 3:
					$keyedResult[$index] = $callBack($row, isset($keyedResult[$index]) ? $keyedResult[$index] : [], $this->columnMeta);
					break;
			}
		}

		$this->reset();

		return $keyedResult;
	}

	public function queryValue($name = null) {
		$data = $this->queryOne();

		if (!$data) {
			return null;
		}

		$data = reset($data);

		if (!is_null($name) && array_key_exists($name, $data)) {
			return $data[$name];
		}

		return reset($data);
	}

	/**
	 * @inheritdoc
	 */
	public function queryOne() {
		$result = $this->statement->fetch(PDO::FETCH_ASSOC);

		if (empty($result) || (is_null($this->columnName) && is_null($this->callBack))) {
			$this->reset();

			return $result;
		}

		$result = [$result];
		$callBack = $this->callBack;

		if (!is_null($this->columnName) && !empty($result) && !isset($result[0][$this->columnName])) {
			throw new ErrorException("Column '{$this->columnName}' does not exist in result set.");
		}

		$keyedResult = [];

		foreach ($result as $index => $row) {
			$index = !is_null($this->columnName) ? $row[$this->columnName] : $index;

			switch ($this->callBackParamsCount) {
				case 0:
					$keyedResult[$index] = $row;
					break;

				case 1:
					$keyedResult[$index] = $callBack($row);
					break;

				case 2:
					$keyedResult[$index] = $callBack($row, isset($keyedResult[$index]) ? $keyedResult[$index] : []);
					break;

				case 3:
					$keyedResult[$index] = $callBack($row, isset($keyedResult[$index]) ? $keyedResult[$index] : [], $this->columnMeta);
					break;
			}
		}

		$this->reset();

		return $keyedResult[0];
	}

	protected function reset() {
		parent::reset();
		$this->columnName = null;
		$this->callBack = null;
		$this->columnMeta = null;
		$this->callBackParamsCount = 0;
	}

	/**
	 * ```
	 * $query = " SELECT
	 *                `client`.client_id,
	 *                `client`.name,
	 *                `client_user`.client_user_id,
	 *                `user`.user_id,
	 *                `user`.username,
	 *                `user_info`.user_info_id,
	 *                `user_info`.value
	 *            FROM `client`
	 *                LEFT JOIN `client_user` USING(client_id)
	 *                LEFT JOIN `user` USING (user_id)
	 *                LEFT JOIN `user_info` USING (user_id)
	 *            WHERE `client`.status_enum = :clientStatus
	 *                AND `client`.role_enum NOT IN (:clientRoles)";
	 *
	 * $result = Engine::$app->db
	 *    ->connect('default')
	 *    ->select($query)
	 *    ->binds([
	 *        ':clientStatus' => Client::STATUS_ENABLED,
	 *    ])
	 *    ->bindArray(':clientRoles', [
	 *        Client::ROLE_ADMIN,
	 *        Client::ROLE_ACCOUNTANT,
	 *    ])
	 *    ->execute()
	 *    ->handler(function ($row, $prevValue, $meta) {
	 *        //var \vendor\ninazu\framework\Component\Db\Meta $meta
	 *        return array_replace_recursive($prevValue, [
	 *            'client_id' => $meta->convert('client_id', $row),
	 *            'name' => $row['name'],
	 *            'users' => (empty($row['user_id'])) ? [] : [
	 *                $row['user_id'] => [
	 *                    'user_id' => $meta->convert('user_id', $row),
	 *                    'username' => $row['username'],
	 *                    'userInfo' => [
	 *                        $row['user_info_id'] => (empty($row['user_info_id'])) ? [] : [
	 *                            'user_info_id' => $meta->convert('user_info_id', $row),
	 *                            'value' => $meta->convert('value', $row),
	 *                        ],
	 *                    ],
	 *                ],
	 *            ],
	 *        ]);
	 *    })
	 *    ->indexBy('client_id')
	 *    ->queryAll();
	 *
	 * $result = SelectQuery::indexCleaner($result, [
	 *    'users' => [
	 *        'userInfo' => [],
	 *    ],
	 * ]);
	 * ```
	 *
	 * @param array $results
	 * @param array $indexes
	 *
	 * @return array
	 */
	public static function indexCleaner(array $results, array $indexes) {
		$results = array_values($results);

		return self::recursiveRunner($results, $indexes);
	}

	private static function recursiveRunner(array $results, array $indexes) {
		if (is_array($indexes)) {
			foreach ($indexes as $k => $value) {
				foreach ($results as $r => $result) {
					$results[$r][$k] = array_values($results[$r][$k]);

					if (is_array($value)) {
						$results[$r][$k] = self::recursiveRunner($results[$r][$k], $value);
					}
				}
			}
		}

		return $results;
	}
}