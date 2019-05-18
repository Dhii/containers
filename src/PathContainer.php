<?php

namespace Dhii\Container;

use Psr\Container\ContainerInterface;
use function array_filter;
use function ltrim;

/**
 * A container implementation that wraps around another to provide access to its values via partial path-like keys.
 *
 * @since [*next-version*]
 */
class PathContainer implements ContainerInterface
{
    /**
     * @var ContainerInterface
     */
    protected $inner;

    /**
     * @var string
     */
    protected $root;

    /**
     * @var string
     */
    protected $delimiter;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param ContainerInterface $inner The container to decorate.
     * @param string             $delimiter
     */
    public function __construct($inner, $delimiter = '/')
    {
        $this->inner = $inner;
        $this->root = '';
        $this->delimiter = $delimiter;
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function get($key)
    {
        $tKey = ltrim($key, $this->delimiter);
        $tRoot = rtrim($this->root, $this->delimiter);
        // Implode to glue together the key and root, and array_filter to ignore them if they're empty
        $fullKey = implode($this->delimiter, array_filter([$tRoot, $tKey]));

        if ($this->inner->has($fullKey)) {
            return $this->inner->get($fullKey);
        }

        $instance = clone $this;
        $instance->root = $fullKey;

        return $instance;
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function has($key)
    {
        return $this->inner->has($key);
    }
}
