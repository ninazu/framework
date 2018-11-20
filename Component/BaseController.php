<?php

namespace vendor\ninazu\framework\Component;

use ReflectionClass;
use vendor\ninazu\framework\Component\Response\Response;
use vendor\ninazu\framework\Component\User\IUser;
use vendor\ninazu\framework\Core\BaseComponent;

abstract class BaseController extends BaseComponent {

	protected $params;

	protected $action;

	protected $response;

	protected $basePath;

	protected $name;

	protected $layout = 'main';

	public function init() {
		$reflection = new ReflectionClass(static::class);
		$this->basePath = dirname(dirname($reflection->getFileName()));
		preg_match('/(.*?)Controller$/', $reflection->getShortName(), $matches);
		$this->name = lcfirst($matches[1]);
	}

	public function runAction($action, $routeParams, $methodParams) {
		$this->action = $action;
		$this->params = array_replace_recursive((array)json_decode(file_get_contents('php://input'), true), $routeParams);
		$this->checkAccess();

		if ($this->beforeAction()) {
			$response = call_user_func_array([$this, 'action' . Router::convertToCamelCase($action)], $methodParams);
			$this->afterAction($response);
		} else {
			$this->getApplication()->response->sendError(Response::STATUS_CODE_PRECONDITION_FAILED, null);
		}
	}

	protected function layoutParams($view, array $params) {
		return $params;
	}

	protected function layout($layout, array $params) {
		extract($params);
		ob_start();
		require "{$this->basePath}/views/layouts/{$layout}.php" . '';
		$layout = ob_get_contents();
		ob_end_clean();

		return $layout;
	}

	public function render($view, array $params, $layout = null) {
		$params = $this->layoutParams($view, $params);
		extract($params);
		ob_start();
		require "{$this->basePath}/views/{$this->name}/{$view}.php" . '';
		$params['content'] = ob_get_contents();
		ob_end_clean();

		if (is_null($layout)) {
			$layout = $this->layout;
		}

		return $this->layout($layout, $params);
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
		} else {
			if (is_null($userRole) || $userRole == IUser::ROLE_GUEST) {
				$statusCode = Response::STATUS_CODE_LOGOUT;
			} else {
				$statusCode = Response::STATUS_CODE_FORBIDDEN;
			}
		}

		return $statusCode;
	}
}