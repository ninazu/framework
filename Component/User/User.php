<?php

namespace vendor\ninazu\framework\Component\User;

use ErrorException;
use vendor\ninazu\framework\Core\BaseComponent;
use vendor\ninazu\framework\Helper\Reflector;

class User extends BaseComponent implements IUser {

	protected $modelClass;

	/**@var IUserIdentity */
	private $model;

	public function init() {
		if (empty($this->modelClass)) {
			throw new ErrorException("User component must be configure 'modelClass'");
		}

		if (!Reflector::isInstanceOf($this->modelClass, IUserIdentity::class)) {
			throw new ErrorException("UserModel must be implement IUserModel");
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