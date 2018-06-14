<?php

namespace vendor\ninazu\framework\Component;

use vendor\ninazu\framework\Component\Response\Response;
use vendor\ninazu\framework\Component\User\IUser;
use vendor\ninazu\framework\Core\BaseComponent;

abstract class BaseController extends BaseComponent {

	protected $params;

	protected $action;

	protected $response;

	public function runAction($action, $params) {
		$this->action = $action;
		$this->params = array_replace_recursive(
			json_decode(file_get_contents('php://input'), true),
			$params
		);

		if ($this->beforeAction()) {
			$this->checkAccess();
			$response = call_user_func_array([$this, 'action' . Router::convertToCamelCase($action)], $params);
			$this->afterAction($response);
		} else {
			$this->getApplication()->response->sendError(Response::STATUS_PRECONDITION_FAILED, null);
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
		$permissions = $this->access();

		//Allow from All if permissions not set
		if (empty($permissions)) {
			return;
		}

		$user = $this->getApplication()->user->getIdentity();
		$userRole = null;

		if (!is_null($user)) {
			$userRole = $user->getRole();
		}

		if (is_null($userRole)) {
			$userRole = IUser::ROLE_GUEST;
		}

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
		$customRole = !in_array($userRole, [
			IUser::ROLE_GUEST,
			IUser::ROLE_AUTHORIZED,
		]);

		$allowedForAuthorised = isset($permissions[IUser::ROLE_AUTHORIZED]);

		if ($allowedForAuthorised && $customRole) {
			return null;
		}

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