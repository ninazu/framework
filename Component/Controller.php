<?php

namespace vendor\ninazu\framework\Component;

use vendor\ninazu\framework\Component\User\IUser;
use vendor\ninazu\framework\Core\Component;

class Controller extends Component {

	private $action;

	private $params;

	private $response;

	public function runAction($action, $params) {
		$this->action = $action;
		$this->params = $params;

		$this->checkAccess();

		if ($this->beforeAction()) {
			$response = call_user_func_array([$this, 'action' . Router::convertToCamelCase($action)], $params);
			$this->afterAction($response);
		}
	}

	/**
	 * @return array
	 */
	protected function access() {
		return [];
	}

	/**
	 * @throws \Exception
	 */
	protected function checkAccess() {
		$user = $this->getApplication()->user;
		$userRole = null;

		if (!is_null($user)) {
			$userRole = $user->getRole();
		}

		if (is_null($userRole)) {
			$userRole = IUser::ROLE_GUEST;
		}

		$permissions = $this->access();
		$actionList = [];

		foreach ($permissions as $permission => $actions) {
			foreach ($actions as $action) {
				$actionList[$action][] = $permission;
			}
		}

		if ($errorCode = $this->getPermissionErrorCode($userRole, $actionList, $permissions)) {
			$this->getApplication()->response->sendError($errorCode, null);
		}
	}

	protected function beforeAction() {
		return true;
	}

	/**
	 * @param $response
	 *
	 * @throws \Exception
	 */
	protected function afterAction($response) {
		$this->getApplication()->response->sendOk($response);
	}

	private function getPermissionErrorCode($userRole, $actionList, $permissions) {
		//Role not found. Deny
		if (!isset($permissions[$userRole])) {
			return $this->permissionScenario($userRole, $actionList);
		}

		//Action not allowed for this role. Deny
		if (!in_array($this->action, $permissions[$userRole])) {
			return $this->permissionScenario($userRole, $actionList);
		}

		//Allowed
		return null;
	}

	private function permissionScenario($userRole, $actionList) {
		if (!array_key_exists($this->action, $actionList)) {
			$statusCode = Response::STATUS_CODE_NOT_FOUND;
		} elseif (is_null($userRole) || $userRole == IUser::ROLE_GUEST) {
			$statusCode = Response::STATUS_CODE_LOGOUT;
		} else {
			$statusCode = Response::STATUS_CODE_FORBIDDEN;
		}

		return $statusCode;
	}
}