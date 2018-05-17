<?php

namespace vendor\ninazu\framework\Component;

use Exception;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use vendor\ninazu\framework\Component\Response\Response;
use vendor\ninazu\framework\Core\Component;

/**
 * @inheritdoc
 */
class Router extends Component {

	protected $rules = [];

	protected $namespace;

	protected $trailingSlash = true;

	/**
	 * @var array $matchTypes Expressions for rules
	 */
	protected static $matchTypes = [
		'i' => '[0-9]++',
		'a' => '[0-9A-Za-z]++',
		's' => '[0-9A-Za-z_\-]++',
		'h' => '[0-9A-Fa-f]++',
		'*' => '.+?',
		'**' => '.++',
		'' => '[^/\.]++',
	];

	private $URL;

	/**
	 * @param bool $skipOnNotFound Send 404 error
	 *
	 * @throws Exception
	 */
	public function execute($skipOnNotFound = false) {
		$this->process($skipOnNotFound);
		//TODO Unload handlers

	}

	public static function convertToCamelCase($string) {
		return str_replace('-', '', ucwords($string, '-'));
	}

	/**
	 * @param $skipOnNotFound
	 * @return mixed|null
	 * @throws Exception
	 */
	private function process($skipOnNotFound) {
		$application = $this->getApplication();
		$url = $application->request->getUrl();

		if (empty($url)) {
			$url = '/';
		}

		if ($this->trailingSlash) {
			$url = rtrim($url, '/') . '/';
		}

		$this->URL = $url;

		foreach ($this->rules as $prefix => $rules) {
			if (strpos($url, $prefix) !== 0) {
				continue;
			}

			foreach ($rules as $pattern => $target) {
				if (preg_match('/^\((.*?)\)/', $pattern, $matches)) {
					$methods = explode("|", $matches[1]);

					if (!in_array($application->request->getMethod(), $methods)) {
						continue;
					}

					$pattern = str_replace($matches[0], '', $pattern);
				}

				if (!$params = $this->checkRule($pattern)) {
					continue;
				}

				list($controllerName, $actionName) = $target;

				$placeholders = [
					'search' => [],
					'replace' => [],
				];

				foreach ($params as $key => $value) {
					if (is_numeric($key)) {
						unset($params[$key]);
					} else {
						$placeholders['search'][] = "{{$key}}";
						$placeholders['replace'][] = $value;
					}
				}

				$controllerName = str_replace($placeholders['search'], $placeholders['replace'], $controllerName);
				$actionName = str_replace($placeholders['search'], $placeholders['replace'], $actionName);
				$controllerName = $this->namespace . 'controllers\\' . self::convertToCamelCase($controllerName) . 'Controller';

				return $this->run($controllerName, $actionName, $params);
			}
		}

		if (!$skipOnNotFound) {
			$this->sendNotFound();
		}

		//NotFound, response not send
		return null;
	}

	/**
	 * Check rule in router config
	 *
	 * @param string $pattern
	 *
	 * @return array|null
	 */
	private function checkRule($pattern) {
		$params = [];

		if ($pattern === '*') {
			//Everyone
			$match = true;
		} elseif (isset($pattern[0]) && $pattern[0] === '@') {
			//Custom regexp
			$pattern = '`' . substr($pattern, 1) . '`u';
			$match = preg_match($pattern, $this->URL, $params);
		} else {
			//Parse pattern
			$n = isset($pattern[0]) ? $pattern[0] : null;
			$route = null;
			$regex = false;
			$j = 0;
			$i = 0;

			// Find the longest non-regex substring and match it against the URI
			while (true) {
				if (!isset($pattern[$i])) {
					break;
				}

				if (false === $regex) {
					$c = $n;
					$regex = $c === '[' || $c === '(' || $c === '.';

					if (false === $regex && false !== isset($pattern[$i + 1])) {
						$n = $pattern[$i + 1];
						$regex = $n === '?' || $n === '+' || $n === '*' || $n === '{';
					}

					if (false === $regex && $c !== '/' && (!isset($this->URL[$j]) || $c !== $this->URL[$j])) {
						return null;
					}

					$j++;
				}

				$route .= $pattern[$i++];
			}

			$regex = self::compileRoute($route);
			$match = preg_match($regex, $this->URL, $params);
		}

		if ($match) {
			return $params;
		}

		return null;
	}

	/** Run action
	 *
	 * @param string $controllerName
	 * @param string $actionName
	 * @param array $routeParams
	 *
	 * @return mixed response
	 *
	 * @throws Exception
	 */
	private function run($controllerName, $actionName, $routeParams) {
		if (!class_exists($controllerName)) {
			$this->sendNotFound();
		};

		/**
		 * Create ControllerComponent without assigning
		 *
		 * @var Controller $controller
		 */
		$controller = new $controllerName($this->getApplication(), []);
		$actionMethodName = 'action' . self::convertToCamelCase($actionName);

		if (!method_exists($controller, $actionMethodName)) {
			$this->sendNotFound();
		}

		$actionParams = self::prepareParams($routeParams, new ReflectionMethod($controllerName, $actionMethodName));
		$response = $controller->runAction($actionName, $actionParams);

		return $response;
	}

	/**
	 * @throws Exception
	 */
	private function sendNotFound() {
		$this->getApplication()->response->sendError(Response::STATUS_CODE_NOT_FOUND, null);
	}

	/**
	 * Compile regexp for regexp :)
	 *
	 * @param string $route
	 *
	 * @return string
	 */
	private static function compileRoute($route) {
		if (preg_match_all('`(/|\.|)\[([^:\]]*+)(?::([^:\]]*+))?\](\?|)`', $route, $matches, PREG_SET_ORDER)) {
			$matchTypes = self::$matchTypes;

			foreach ($matches as $match) {
				list($block, $pre, $type, $param, $optional) = $match;

				if (isset($matchTypes[$type])) {
					$type = $matchTypes[$type];
				}

				if ($pre === '.') {
					$pre = '\.';
				}

				if ($param !== '') {
					$param = "?P<{$param}>";
				}

				if ($optional !== '') {
					$optional = '?';
				}

				$pattern = "(?:{$pre}({$param}{$type})){$optional}";
				$route = str_replace($block, $pattern, $route);
			}
		}

		return "`^{$route}$`u";
	}

	/**
	 * Prepare params for action
	 *
	 * @param $params
	 * @param ReflectionFunctionAbstract $reflection
	 *
	 * @return array
	 */
	private static function prepareParams($params, ReflectionFunctionAbstract $reflection) {
		$closureParams = [];
		$arguments = $reflection->getParameters();

		foreach ($arguments as $argument) {
			if (isset($params[$argument->name])) {
				$closureParams[] = $params[$argument->name];
			} else {
				$closureParams[] = $argument->isDefaultValueAvailable() ? $argument->getDefaultValue() : null;
			}
		}

		return $closureParams;
	}
}