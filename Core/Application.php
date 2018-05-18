<?php

namespace vendor\ninazu\framework\Core;

use ErrorException;
use ReflectionClass;
use ReflectionException;
use vendor\ninazu\framework\Component\Db\Interfaces\IMysql;
use vendor\ninazu\framework\Component\Db\Mysql;
use vendor\ninazu\framework\Component\Request;
use vendor\ninazu\framework\Component\Response\Response;
use vendor\ninazu\framework\Component\Router;
use vendor\ninazu\framework\Component\User\User;
use vendor\ninazu\framework\Core\Handler\DefaultHandler;
use vendor\ninazu\framework\Core\Handler\IHandler;

/**
 * @property Request $request
 * @property Response $response
 * @property Router $router
 * @property User $user
 * @property IMysql $db
 * */
abstract class Application {

	public static $app;

	private $basePath;

	private $encoding = 'UTF-8';

	private $components = [];

	private $initialized = false;

	/**
	 * Core constructor.
	 *
	 * @param array $autoLoaders
	 * @param IHandler|null $handler
	 *
	 * @throws \Exception
	 */
	public function __construct(array $autoLoaders, IHandler $handler = null) {
		//Prevent error printing
		ini_set('display_errors', false);

		if (is_null($handler)) {
			require_once __DIR__ . '/Handler/DefaultHandler.php';

			$handler = new DefaultHandler($this);
		}

		spl_autoload_register([$handler, 'handlerAutoLoader'], true, true);
		$handler->setAutoLoader($autoLoaders);

		register_shutdown_function([$handler, 'handlerShutdown']);
		set_error_handler([$handler, 'handlerError']);
		set_exception_handler([$handler, 'handlerException']);
		$this->setDefaultComponents();

		$this::$app = $this;
	}

	/**
	 * Delayed loader of component
	 *
	 * @param $name
	 *
	 * @return null
	 *
	 * @throws ErrorException
	 * @throws ReflectionException
	 */
	public function __get($name) {
		if (!isset($this->components[$name])) {
			return null;
		}

		//Assign component to application
		if (!$this->components[$name]['initialized']) {
			$className = $this->components[$name]['class'];

			if ((new ReflectionClass($className))->isAbstract()) {
				throw new ErrorException("Component '{$name}' must be extend");
			}

			$this->$name = new $className($this, $this->components[$name]['config']);
			$this->components[$name]['initialized'] = true;
		}

		return $this->$name;
	}

	/**
	 * Getter of basePath
	 *
	 * @return string
	 */
	public function getBasePath() {
		return $this->basePath;
	}

	/**
	 * Setup application and return router
	 *
	 * @param callable $configCallback
	 *
	 * @return Router
	 *
	 * @throws ErrorException
	 */
	public function setup(callable $configCallback) {
		if ($this->initialized) {
			throw new ErrorException('Application already initialized');
		}

		$this->initialized = true;
		$config = $configCallback();

		if (isset($config['basePath'])) {
			$this->basePath = realpath($config['basePath']);
		}

		if (isset($config['encoding'])) {
			$this->encoding = $config['encoding'];
		}

		$this->setEncoding();

		foreach ($config['components'] as $name => $params) {
			$params = array_replace_recursive(is_array($this->components[$name]) ? $this->components[$name] : [], $params);

			if (!array_key_exists('class', $params) || empty($params['class']) || !is_string($params['class'])) {
				throw new ErrorException("Missing 'class' of component '{$name}'");
			}

			//Delayed component loader
			$this->components[$name] = [
				'initialized' => false,
				'class' => $params['class'],
				'config' => isset($params['config']) ? $params['config'] : [],
			];
		}

		return $this->router;
	}

	protected function setDefaultComponents() {
		$components = [
			'response' => Response::class,
			'router' => Router::class,
			'request' => Request::class,
			'user' => User::class,
			'db' => Mysql::class,
		];

		foreach ($components as $name => $class) {
			$this->components[$name] = [
				'initialized' => false,
				'class' => $class,
				'config' => [],
			];
		}
	}

	protected function setEncoding() {
		mb_internal_encoding($this->encoding);
		mb_http_output($this->encoding);
		mb_http_input($this->encoding);
		mb_regex_encoding($this->encoding);
	}
}
