<?php

namespace vendor\ninazu\framework\Component\User;

use RuntimeException;
use vendor\ninazu\framework\Core\BaseComponent;
use vendor\ninazu\framework\Helper\Reflector;

class User extends BaseComponent implements IUser {

	protected $modelClass;

	/**@var IUserIdentity */
	private $model;

	public function init() {
		if (empty($this->modelClass)) {
			throw new RuntimeException("User component must be configure 'modelClass'");
		}

		if (!Reflector::isInstanceOf($this->modelClass, IUser::class)) {
			throw new RuntimeException("UserModel must be implement IUser");
		}

		$this->setIdentity(new $this->modelClass());
	}

	/**
	 * @return IUserIdentity
	 */
	public function getIdentity() {
		return $this->model;
	}

	public function setIdentity(IUserIdentity $identity) {
		$this->model = $identity;
	}
}