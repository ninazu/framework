<?php

namespace vendor\ninazu\framework\Component;

use ReflectionClass;
use vendor\ninazu\framework\Component\Response\IResponse;
use vendor\ninazu\framework\Component\Response\Response;
use vendor\ninazu\framework\Component\User\IUserIdentity;
use vendor\ninazu\framework\Core\BaseComponent;
use vendor\ninazu\framework\Helper\Formatter;

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
		$this->params = array_replace_recursive(
			$this->getApplication()->request->getParams(),
			(array)json_decode(file_get_contents('php://input'), true),
			$routeParams
		);
		$this->checkAccess();

		if ($this->beforeAction()) {
			$response = call_user_func_array([$this, 'action' . Formatter::dashToCamelCase($action)], $methodParams);
			$this->afterAction($response);
		} else {
			return $this->getApplication()->response->sendError(Response::STATUS_CODE_PRECONDITION_FAILED, null);
		}

		return true;
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
	 */
	protected function checkAccess(): bool {
		$permissions = $this->access();

		//Allow from All if permissions not set
		if (empty($permissions)) {
			return false;
		}

		$user = $this->getApplication()->user->getIdentity();
		$userRole = null;

		if (!is_null($user)) {
			$userRole = $user->getRole();
		}

		if (is_null($userRole)) {
			$userRole = IUserIdentity::ROLE_GUEST;
		}

		$actionList = [];

		foreach ($permissions as $permission => $actions) {
			foreach ($actions as $action) {
				$actionList[$action][] = $permission;
			}
		}

		if ($errorCode = $this->getPermissionErrorCode($userRole, $actionList, $permissions)) {
			return $this->getApplication()->response->sendError($errorCode, null);
		}

		return true;
	}

	protected function beforeAction(): bool {
		return true;
	}

	/**
	 * @param $response
	 *

	 */
	protected function afterAction($response) {
		$this->getApplication()->response->sendOk($response);
	}

	private function getPermissionErrorCode($userRole, $actionList, $permissions) {
		$customRole = !in_array($userRole, [
			IUserIdentity::ROLE_GUEST,
			IUserIdentity::ROLE_AUTHORIZED,
		]);

		$allowedForAuthorised = isset($permissions[IUserIdentity::ROLE_AUTHORIZED]);

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
			if (is_null($userRole) || $userRole == IUserIdentity::ROLE_GUEST) {
				$statusCode = Response::STATUS_CODE_LOGOUT;
			} else {
				$statusCode = Response::STATUS_CODE_FORBIDDEN;
			}
		}

		return $statusCode;
	}
}