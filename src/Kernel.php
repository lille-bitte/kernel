<?php

declare(strict_types=1);

namespace LilleBitte\Kernel;

use LogicException;
use ReflectionClass;
use LilleBitte\Annotations\AnnotationReader;
use LilleBitte\Container\ContainerBuilder;
use LilleBitte\Container\ContainerInterface;
use LilleBitte\Emitter\Emitter;
use LilleBitte\Emitter\EmitterInterface;
use LilleBitte\Kernel\Controller\ControllerInterface;
use LilleBitte\Routing\RouterFactory;
use LilleBitte\Routing\RouterInterface;
use LilleBitte\Routing\Annotation\Route;
use LilleBitte\Routing\Annotation\Method;
use Psr\Http\Message\RequestInterface;

use function count;
use function explode;
use function get_class_methods;
use function glob;
use function is_subclass_of;

/**
 * @author Paulus Gandung Prakosa <rvn.plvhx@gmail.com>
 */
abstract class Kernel implements KernelInterface
{
    /**
     * @var ContainerInterface
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

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var EmitterInterface
     */
    private $emitter;

    public function __construct(RouterInterface $router = null, EmitterInterface $emitter = null)
    {
        $this->router  = $router ?? RouterFactory::getRouter();
        $this->emitter = $emitter ?? new Emitter();
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
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->setCacheDir($this->getRootDirectory() . '/var/cache');
        $containerBuilder->setCacheFile('container.cache.php');

        // initialize registered extensions.
        $this->initializeExtensions();

        // build each registered extensions.
        foreach ($this->extensions as $ext) {
            $ext->build($containerBuilder);
        }

        // compile current container
        $containerBuilder->compile();

        // build and set current compiled container
        $this->setContainer($containerBuilder->build());
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
     * Set dependency injection container.
     *
     * @return ContainerInterface
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Process request and send it's response back
     * to the callee.
     *
     * @return void
     */
    public function send()
    {
        $router  = $this->getRouter();
        $emitter = $this->getEmitter();
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
        $router = $this->getRouter();

        // restore route group before
        // get route metadata from
        // annotation.
        $router->resetGroup();

        foreach ($controllers as $controller) {
            $name    = basename($controller, ".php");
            $fqcn    = $namespace . "\\" . $name;
            $object  = new $fqcn();
            $methods = get_class_methods($object);

            foreach ($methods as $method) {
                $refl  = (new ReflectionClass($object))->getMethod($method);
                $route = $reader->getMethodAnnotation($refl, Route::class);

                if (null === $route) {
                    continue;
                }

                if (is_subclass_of($object, ControllerInterface::class)) {
                    $object->setContainer($this->getContainer());
                }

                $methodObj = $reader->getMethodAnnotation($refl, Method::class);
                $router->any(
                    is_null($methodObj) ? ['GET'] : $methodObj->getMethods(),
                    $route->getRoute(),
                    [$object, $method]
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
     * Get router object.
     *
     * @return RouterInterface
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * Get emitter object.
     *
     * @return EmitterInterface
     */
    public function getEmitter()
    {
        return $this->emitter;
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
