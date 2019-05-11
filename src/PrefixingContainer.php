<?php

namespace Dhii\Container;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * A container implementation that wraps around an inner container and adds prefixes to its keys, requiring consumers
 * to include them when fetching or looking up data.
 *
 * @since [*next-version*]
 */
class PrefixingContainer implements ContainerInterface
{
    /**
     * @var ContainerInterface
     */
    protected $inner;

    /**
     * @var string
     */
    protected $prefix;

    /**
     * @var bool
     */
    protected $strict;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param ContainerInterface $container The container whose keys to prefix.
     * @param string             $prefix    The prefix to apply to the container's keys.
     * @param bool               $strict    Whether or not to fallback to un-prefixed keys if a prefixed key does not
     *                                      exist in the inner container.
     */
    public function __construct(ContainerInterface $container, string $prefix, bool $strict = true)
    {
        $this->inner = $container;
        $this->prefix = $prefix;
        $this->strict = $strict;
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function get($key)
    {
        try {
            return $this->inner->get($this->getInnerKey($key));
        } catch (NotFoundExceptionInterface $nfException) {
            if ($this->strict) {
                throw $nfException;
            }
        }

        return $this->inner->get($key);
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function has($key)
    {
        return $this->inner->has($this->getInnerKey($key)) || (!$this->strict && $this->inner->has($key));
    }

    /**
     * Retrieves the key to use for the inner container.
     *
     * @since [*next-version*]
     *
     * @param string $key The outer key.
     *
     * @return string The inner key.
     */
    protected function getInnerKey($key)
    {
        $prefixLength = strlen($this->prefix);

        return ($prefixLength > 0 && strpos($key, $this->prefix) === 0)
            ? substr($key, $prefixLength)
            : $key;
    }
}
