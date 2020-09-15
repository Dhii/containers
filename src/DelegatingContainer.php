<?php

namespace Dhii\Container;

use Dhii\Collection\ContainerInterface;
use Dhii\Container\Exception\ContainerException;
use Dhii\Container\Exception\NotFoundException;
use Dhii\Container\Util\StringTranslatingTrait;
use Interop\Container\ServiceProviderInterface;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use UnexpectedValueException;

class DelegatingContainer implements ContainerInterface
{
    use StringTranslatingTrait;

    /**
     * @var ServiceProviderInterface
     */
    protected $provider;

    /**
     * @var PsrContainerInterface|null
     */
    protected $parent;

    /**
     * @param ServiceProviderInterface   $provider
     * @param PsrContainerInterface|null $parent
     */
    public function __construct(ServiceProviderInterface $provider, PsrContainerInterface $parent = null)
    {
        $this->provider = $provider;
        $this->parent = $parent;
    }

    /**
     * {@inheritDoc}
     */
    public function get($id)
    {
        static $stack = [];

        if (array_key_exists($id, $stack)) {
            $trace = implode(' -> ', array_keys($stack)) . ' -> ' . $id;

            throw new ContainerException(
                $this->__("Circular dependency detected:\n%s", [$trace]),
                0,
                null
            );
        }

        $stack[$id] = true;

        try {
            return $this->_createService($id);
        } finally {
            unset($stack[$id]);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function has($id)
    {
        $services = $this->provider->getFactories();

        return array_key_exists($id, $services);
    }

    /**
     * Creates a service, using the factory that corresponds to a specific key.
     *
     * @since [*next-version*]
     *
     * @param string $key The key of the service to be created.
     *
     * @return mixed The created service.
     *
     * @throws NotFoundException If no factory corresponds to the given $key.
     * @throws ContainerException If an error occurred while creating the service.
     */
    protected function _createService(string $key)
    {
        $provider = $this->provider;
        $services = $provider->getFactories();

        if (!array_key_exists($key, $services)) {
            throw new NotFoundException(
                $this->__('Service not found for key "%1$s"', [$key]),
                0,
                null
            );
        }

        $service = $services[$key];

        try {
            $service = $this->_invokeFactory($service);
        } catch (UnexpectedValueException $e) {
            throw new ContainerException(
                $this->__('Could not create service "%1$s"', [$key]),
                0,
                $e
            );
        }

        $extensions = $provider->getExtensions();

        if (!array_key_exists($key, $extensions)) {
            return $service;
        }

        $extension = $extensions[$key];

        try {
            $service = $this->_invokeExtension($extension, $service);
        } catch (UnexpectedValueException $e) {
            throw new ContainerException(
                $this->__('Could not extend service "%1$s"', [$key]),
                0,
                $e
            );
        }

        return $service;
    }

    /**
     * Retrieves a service by invoking its factory.
     *
     * @param callable $factory The factory to invoke.
     *
     * @return mixed The service created by the factory.
     *
     * @throws UnexpectedValueException If factory could not be invoked.
     */
    protected function _invokeFactory(callable $factory)
    {
        if (!is_callable($factory)) {
            throw new UnexpectedValueException(
                $this->__('Factory could not be invoked'),
                0,
                null
            );
        }

        $baseContainer = $this->_getBaseContainer();
        $service = $factory($baseContainer);

        return $service;
    }

    /**
     * Extends the service by invoking the extension with it.
     *
     * @param callable $extension The extension to invoke.
     * @param mixed $service The service to extend.
     *
     * @return mixed The extended service.
     *
     * @throws UnexpectedValueException If extension cannot be invoked.
     */
    protected function _invokeExtension(callable $extension, $service)
    {
        if (!is_callable($extension)) {
            throw new UnexpectedValueException(
                $this->__('Factory could not be invoked'),
                0,
                null
            );
        }

        $baseContainer = $this->_getBaseContainer();
        $service = $extension($baseContainer, $service);

        return $service;
    }

    /**
     * Retrieves the container to be used for definitions and extensions.
     *
     * @return PsrContainerInterface The parent container, if set. Otherwise, this instance.
     */
    protected function _getBaseContainer() : PsrContainerInterface
    {
        return $this->parent instanceof PsrContainerInterface
            ? $this->parent
            : $this;
    }
}
