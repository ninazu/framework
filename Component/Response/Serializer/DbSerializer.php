<?php

namespace vendor\ninazu\framework\Component\Response\Serializer;

use ErrorException;
use vendor\ninazu\framework\Component\Db\Interfaces\IMysql;
use vendor\ninazu\framework\Component\Response\BaseSerializer;

class DbSerializer extends BaseSerializer {

	protected $db;

	protected $connectionName;

	protected $table;

	protected $column;

	public function init() {
		if (empty($this->db) || empty($this->connectionName) || empty($this->table) || empty($this->column)) {
			throw new ErrorException('Invalid config');
		}
	}

	public function serialize() {
		$app = $this->getApplication();
		$response = $app->response;
		$data = $response->getData();
		$extra = $response->getExtra();

		if (!empty($data) || !empty($extra)) {
			//TODO WTF
			/**@var IMysql $db */
			$db = $app->{$this->db};

			$db->connect($this->connectionName)
				->insert($this->table, [
					[
						$this->column => print_r([
							'data' => $data,
							'extra' => $extra,
						], true),
					],
				])
				->execute();
		}

		return '';
	}
}