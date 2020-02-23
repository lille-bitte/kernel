<?php

declare(strict_types=1);

namespace LilleBitte\Kernel;

use ReflectionClass;
use LilleBitte\Annotations\ClassRegistry;
use LilleBitte\Annotations\AnnotationReader;
use LilleBitte\Container\ContainerBuilder;
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
	private $extensions = [];

	public function __construct(RequestInterface $request)
	{
		$this->setRequest($request);
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
	}

	/**
	 * Get associated dependency injection container.
	 *
	 * @return LilleBitte\Container\ContainerBuilderInterface
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
		$router  = $this->container->get('core.router');
		$emitter = $this->container->get('core.emitter');
		$resp    = $router->dispatch($this->getRequest());

		if ($resp->getStatus() !== 0) {
			// TODO: throw an exception.
			return;
		}

		$emitter->emit($resp->getResponse());
	}
}