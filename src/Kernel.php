<?php

declare(strict_types=1);

namespace LilleBitte\Kernel;

use LogicException;
use ReflectionClass;
use LilleBitte\Annotations\ClassRegistry;
use LilleBitte\Annotations\AnnotationReader;
use LilleBitte\Container\ContainerBuilder;
use LilleBitte\Routing\Annotation\Route;
use LilleBitte\Routing\Annotation\Method;
use Psr\Http\Message\RequestInterface;

use function count;
use function explode;
use function get_class_methods;
use function glob;

/**
 * @author Paulus Gandung Prakosa <rvn.plvhx@gmail.com>
 */
abstract class Kernel implements KernelInterface
{
	/**
	 * @var ContainerBuilder
	 */
	private $container;

	/**
	 * @var RequestInterface
	 */
	private $request;

	/**
	 * @var array
	 */
	private $extensions;

	public function __construct()
	{
		$this->buildContainer();
		$this->configureRoute();
	}

	/**
	 * Build and configure dependency injection
	 * container.
	 *
	 * @return void
	 */
	public function buildContainer()
	{
		$this->container = new ContainerBuilder();
		$this->container->setCacheDir($this->getRootDirectory() . '/var/cache');

		// initialize registered extensions.
		$this->initializeExtensions();

		// build each registered extensions.
		foreach ($this->extensions as $ext) {
			$ext->build($this->container);
		}

		// compile current container
		$this->container->compile();
	}

    /**
     * Get associated dependency injection container.
     *
     * @return ContainerBuilderInterface
     */
	public function getContainer()
	{
		return $this->container;
	}

	/**
	 * Process request and send it's response back
	 * to the callee.
	 *
	 * @return void
	 */
	public function send()
	{
		$router  = $this->container->get('extension.framework.router_factory');
		$emitter = $this->container->get('extension.framework.emitter');
		$resp    = $router->dispatch($this->request);

		if ($resp->getStatus() !== 0) {
			// TODO: throw an exception.
			return;
		}

		$emitter->emit($resp->getResponse());
	}

	/**
	 * {@inheritdoc}
	 */
	public function registerAnnotationClasses(array $classes)
	{
		foreach ($classes as $class) {
			ClassRegistry::register($class);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function handle(RequestInterface $request)
	{
		$this->request = $request;
	}

    /**
     * Populate route from controller annotation.
     *
     * @param string $namespace Controller namespace.
     * @param string $controllerDir Controller directory.
     * @return void
     * @throws \ReflectionException
     */
	protected function populateRouteFromControllerAnnotation($namespace, $controllerDir)
	{
		$controllers = glob(
			sprintf(
				"%s/%s/*.php",
				rtrim($this->getRootDirectory(), "/"),
				rtrim($controllerDir, "/")
			)
		);
		$reader = new AnnotationReader();
		$router = $this->getContainer()->get('extension.framework.router_factory');

		// restore route group before
		// get route metadata from
		// annotation.
		$router->resetGroup();

		foreach ($controllers as $controller) {
			$name    = basename($controller, ".php");
			$fqcn    = $namespace . "\\" . $name;
			$methods = get_class_methods($fqcn);

			foreach ($methods as $method) {
				$refl  = (new ReflectionClass($fqcn))->getMethod($method);
				$route = $reader->getMethodAnnotation($refl, Route::class);

				if (null === $route) {
					continue;
				}

				$methodObj = $reader->getMethodAnnotation($refl, Method::class);
				$router->any(
					is_null($methodObj) ? ['GET'] : $methodObj->getMethods(),
					$route->getRoute(),
					[$fqcn, $method]
				);
			}
		}
	}

	/**
	 * Initialize registered extensions.
	 *
	 * @return void
	 * @throws LogicException if two or more extension share a common name.
	 */
	protected function initializeExtensions()
	{
		$this->extensions = [];

		foreach ($this->registerExtensions() as $ext) {
			$name = $ext->getExtensionName();

			if (isset($this->extensions[$name])) {
				throw new LogicException(
					sprintf(
						"Extension with name (%s) exists.",
						$name
					)
				);
			}

			$this->extensions[$name] = $ext;
		}
	}

	/**
	 * Get root directory.
	 *
	 * @return string
	 */
	abstract public function getRootDirectory();

	/**
	 * Configure router object.
	 *
	 * @return void
	 */
	abstract protected function configureRoute();

	/**
	 * Populate route from configuration file.
	 *
	 * @return void
	 */
	abstract protected function populateRouteFromConfig();
}
