<?php

namespace Dhii\Container;

use Dhii\Collection\ContainerInterface;
use Dhii\Container\Exception\NotFoundException;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * A container implementation that wraps around an inner container and prefixes its keys, requiring consumers to
 * include them when fetching or looking up data.
 *
 * @since [*next-version*]
 */
class PrefixingContainer implements ContainerInterface
{
    /**
     * @since [*next-version*]
     *
     * @var PsrContainerInterface
     */
    protected $inner;

    /**
     * @since [*next-version*]
     *
     * @var string
     */
    protected $prefix;

    /**
     * @since [*next-version*]
     *
     * @var bool
     */
    protected $strict;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param PsrContainerInterface $container The container whose keys to prefix.
     * @param string                $prefix    The prefix to apply to the container's keys.
     * @param bool                  $strict    Whether or not to fallback to un-prefixed keys if a prefixed key does not
     *                                         exist in the inner container.
     */
    public function __construct(PsrContainerInterface $container, string $prefix, bool $strict = true)
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
        if (!$this->isPrefixed($key) && $this->strict) {
            throw new NotFoundException(sprintf('Key "%s" does not exist', $key), 0, null, $this, $key);
        }

        try {
            return $this->inner->get($this->unprefix($key));
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
        if (!$this->isPrefixed($key) && $this->strict) {
            return false;
        }

        return $this->inner->has($this->unprefix($key)) || (!$this->strict && $this->inner->has($key));
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
    protected function unprefix($key)
    {
        return $this->isPrefixed($key)
            ? substr($key, strlen($this->prefix))
            : $key;
    }

    /**
     * Checks if the key is prefixed.
     *
     * @since [*next-version*]
     *
     * @param string $key The key to check.
     *
     * @return bool True if the key is prefixed, false if not.
     */
    protected function isPrefixed($key)
    {
        return strlen($this->prefix) > 0 && strpos($key, $this->prefix) === 0;
    }
}
