<?php declare(strict_types=1);

namespace Dhii\Container;

use Dhii\Collection\ContainerInterface;
use Dhii\Container\Exception\ContainerException;
use Dhii\Container\Exception\NotFoundException;
use Dhii\Container\Util\StringTranslatingTrait;
use Exception;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Caches entries from an internal container.
 *
 * @package Dhii\Container1
 */
class CachingContainer implements ContainerInterface
{
    use StringTranslatingTrait;

    /**
     * @var array
     */
    protected $cache;
    /**
     * @var PsrContainerInterface
     */
    protected $container;

    /**
     * @param PsrContainerInterface $container The container to cache entries from.
     */
    public function __construct(PsrContainerInterface $container)
    {
        $this->container = $container;
        $this->cache = [];
    }

    /**
     * {@inheritDoc}
     */
    public function get($key)
    {
        $key = (string) $key;

        try {
            $value = $this->_getCached($key, function () use ($key) {
                return $this->container->get($key);
            });
        } catch (NotFoundExceptionInterface $e) {
            throw new NotFoundException(
                $this->__('Key "%1$s" not found in inner container', [$key]),
                0,
                $e
            );
        } catch (Exception $e) {
            throw new ContainerException(
                $this->__('Could not retrieve value for key "%1$s from inner container', [$key]),
                0,
                $e
            );
        }

        return $value;
    }

    /**
     * {@inheritDoc}
     */
    public function has($key)
    {
        $key = (string) $key;

        try {
            if ($this->_hasCached($key)) {
                return true;
            }
        } catch (Exception $e) {
            throw new ContainerException(
                $this->__('Could not check cache for key "%1$s"', [$key]),
                0,
                $e
            );
        }

        try {
            if ($this->container->has($key)) {
                return true;
            }
        } catch (Exception $e) {
            throw new ContainerException(
                $this->__('Could not check inner container for key "%1$s"', [$key]),
                0,
                $e
            );
        }

        return false;
    }

    /**
     * Retrieves a value by key from cache, creating it if it does not exist.
     *
     * @param string $key The key to get.
     * @param callable $generator Creates the value.
     *
     * @return mixed The cached value.
     *
     * @throws Exception If problem caching.
     */
    protected function _getCached(string $key, callable $generator)
    {
        if (!array_key_exists($key, $this->cache)) {
            $value = $this->_invokeGenerator($generator);
            $this->cache[$key] = $value;
        }

        return $this->cache[$key];
    }

    /**
     * Checks the cache for the specified key.
     *
     * @param string $key The key to check for.
     *
     * @return bool True if cache contains the value; false otherwise.
     *
     * @throws Exception If problem checking.
     */
    protected function _hasCached(string $key)
    {
        return array_key_exists($key, $this->cache);
    }

    /**
     * Generates a value by invoking the generator.
     *
     * @param callable $generator Generates a value.
     *
     * @return mixed The generated result.
     *
     * @throws Exception If problem generating.
     */
    protected function _invokeGenerator(callable $generator)
    {
        $result = $generator();

        return $result;
    }
}
