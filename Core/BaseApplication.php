<?php

namespace vendor\ninazu\framework\Core;

use Exception;
use RuntimeException;
use ReflectionClass;
use ReflectionException;
use vendor\ninazu\framework\Component\Db\Interfaces\IMysql;
use vendor\ninazu\framework\Component\Db\Mysql;
use vendor\ninazu\framework\Component\Mail\Mail;
use vendor\ninazu\framework\Component\Request;
use vendor\ninazu\framework\Component\Response\Response;
use vendor\ninazu\framework\Component\Router;
use vendor\ninazu\framework\Component\Translate;
use vendor\ninazu\framework\Component\User\User;
use vendor\ninazu\framework\Core\Handler\DefaultHandler;
use vendor\ninazu\framework\Core\Handler\IHandler;

/**
 * @property Request $request
 * @property Response $response
 * @property Router $router
 * @property User $user
 * @property IMysql $db
 * @property Translate $lang
 * @property Mail $mail
 * */
abstract class BaseApplication {

	public static $app;

	protected string $basePath;

	protected string $encoding = 'UTF-8';

	protected string $adminEmail;

	protected array $components = [];

	protected bool $initialized = false;

	/**
	 * Core constructor.
	 *
	 * @param array $autoLoaders
	 * @param IHandler|null $handler
	 *
	 * @throws Exception
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

	public function __destruct() {
//TODO
//		restore_exception_handler();
//		restore_error_handler();
	}

	/**
	 * Delayed loader of component
	 *
	 * @param $name
	 *
	 * @return null
	 *
	 * @throws ReflectionException
	 */
	public function __get(string $name) {
		if (!isset($this->components[$name])) {
			return null;
		}

		//Assign component to application
		if (!$this->components[$name]['initialized']) {
			$className = $this->components[$name]['class'];

			if ((new ReflectionClass($className))->isAbstract()) {
				throw new RuntimeException("Component '{$name}' must be extend");
			}

			$this->$name = new $className($this, $this->components[$name]['config']);
			$this->components[$name]['initialized'] = true;
		}

		return $this->$name;
	}
//
//	public function getComponentConfig(string $name): array {
//		if (!Environment::isTest()) {
//			throw new RuntimeException('getComponentConfig allow only for Environment::TEST');
//		}
//
//		return $this->components[$name]['config'];
//	}

	/**
	 * Getter of basePath
	 *
	 * @return string
	 */
	public function getBasePath() {
		return $this->basePath;
	}

	/**
	 * Getter of basePath
	 *
	 * @return string
	 */
	public function getAdminEmail() {
		return $this->adminEmail;
	}

	/**
	 * Setup application and return router
	 *
	 * @param callable $configCallback
	 *
	 * @return Router
	 *
	 * @throws \Exception
	 */
	public function setup(callable $configCallback) {
		if ($this->initialized) {
			throw new RuntimeException('Application already initialized');
		}

		$this->initialized = true;
		$config = $configCallback($this);

		if (isset($config['environments']) && Environment::isInitialized() && array_key_exists(Environment::getEnvironment(), $config['environments'])) {
			$config = array_replace_recursive($config, $config['environments'][Environment::getEnvironment()]);
		}

		if (isset($config['adminEmail'])) {
			$this->adminEmail = $config['adminEmail'];
		}

		if (isset($config['basePath'])) {
			$this->basePath = realpath($config['basePath']);
		}

		if (isset($config['encoding'])) {
			$this->encoding = $config['encoding'];
		}

		$this->setEncoding();

		foreach ($config['components'] as $name => $params) {
			$params = array_replace_recursive(isset($this->components[$name]) && is_array($this->components[$name]) ? $this->components[$name] : [], $params);

			if (!array_key_exists('class', $params) || empty($params['class']) || !is_string($params['class'])) {
				throw new RuntimeException("Missing 'class' of component '{$name}'");
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
			'lang' => Translate::class,
			'mail' => Mail::class,
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
