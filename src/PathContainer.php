<?php

namespace Dhii\Container;

use Dhii\Container\Exception\NotFoundException;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * A container implementation that decorates a hierarchy of {@link ContainerInterface} instances to allow path-like
 * access to deep containers or data.
 *
 * **Example usage**
 *
 * Consider the below hierarchy of containers:
 *
 * ```php
 * $container = new Container([
 *      'config' => new Container([
 *          'db' => new Container([
 *              'host' => 'localhost',
 *              'port' => 3306
 *          ])
 *      ])
 * ]);
 * ```
 *
 * A {@link PathContainer} can decorate the `$container` to substitute this:
 *
 * ```php
 * $host = $container->get('config')->get('db')->get('port');
 * ```
 *
 * With this:
 *
 * ```php
 * $pContainer = new PathContainer($container, '.');
 * $pContainer->get('config.db.port');
 * ```
 *
 * Note that this implementation DOES NOT create containers for hierarchical _values_. Each segment in a given path
 * must correspond to a child {@link ContainerInterface} instance.
 *
 * @since [*next-version*]
 * @see   SegmentingContainer For an implementation that achieves the opposite effect.
 */
class PathContainer implements ContainerInterface
{
    /**
     * @since [*next-version*]
     *
     * @var ContainerInterface
     */
    protected $inner;

    /**
     * @since [*next-version*]
     *
     * @var string
     */
    protected $delimiter;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param ContainerInterface $inner     The container instance to decorate.
     * @param string             $delimiter The path delimiter to use.
     */
    public function __construct(ContainerInterface $inner, string $delimiter = '/')
    {
        $this->inner = $inner;
        $this->delimiter = $delimiter;
    }

    /**
     * @inheritDoc
     *
     * @since [*next-version*]
     */
    public function get($key)
    {
        $tKey = ltrim($key, $this->delimiter);
        $path = array_filter(explode($this->delimiter, $tKey));

        if (empty($path)) {
            throw new NotFoundException("The path is empty");
        }

        $current = $this->inner;
        $head = $path[0];

        while (!empty($path)) {
            if (!($current instanceof ContainerInterface)) {
                $tail = implode($this->delimiter, $path);
                throw new NotFoundException("Key '{$head}' does not exist at path '{$tail}'", 0, null, $this, $head);
            }

            $head = array_shift($path);
            $current = $current->get($head);
        }

        return $current;
    }

    /**
     * @inheritDoc
     *
     * @since [*next-version*]
     */
    public function has($key)
    {
        try {
            $this->get($key);

            return true;
        } catch (NotFoundExceptionInterface $exception) {
            return false;
        }
    }
}
