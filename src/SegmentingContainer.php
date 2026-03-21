<?php

declare(strict_types=1);

namespace Dhii\Container;

use Dhii\Collection\ContainerInterface;
use Psr\Container\ContainerInterface as PsrContainerInterface;

use function array_filter;
use function ltrim;

/**
 * This container implementation decorates another to provide nested container access even when the decorated
 * container's internal data is flat.
 *
 * Segmenting containers are intended to be used with keys that contain segments, i.e. keys that use a delimiter to
 * indicate hierarchy. For example: "some/test/key" or "some.deep.config.value". The delimiter can be configured during
 * construction of a segmenting container.
 *
 * A segmenting container can yield 2 different kinds of results when {@link SegmentingContainer::get()} is called:
 *
 * **Values**
 *
 * If the inner container has a value for the given key, that value is returned.
 *
 * **Segments**
 *
 * If the inner container has no value for the given key, a new {@link SegmentingContainer} instance is returned. This
 * segmenting container will be aware of the key that resulted in its creation, and will automatically prepend that key
 * to parameter keys given in `get()`.
 *
 * **Example usage:**
 *
 * Consider the below data and a regular `$container` that provides access to it:
 *
 * ```php
 * $data = [
 *     'config.db.host' => 'localhost',
 *     'config.db.post' => '3306',
 * ];
 * ```
 *
 * A segmenting container can be created that provides access to the "host" and "port":
 *
 * ```php
 * $segmented = new SegmentingContainer($container, '.');
 * $dbConfig = $config->get('config')->get('db');
 * $dbConfig->get("host"); // "localhost"
 * $dbConfig->get("port"); // 3306
 * ```
 *
 * @see   PathContainer For an implementation that achieves the opposite effect.
 */
class SegmentingContainer implements ContainerInterface
{
    /**
     * @var PsrContainerInterface
     */
    protected PsrContainerInterface $inner;

    /**
     * @var string
     */
    protected string $root;

    /**
     * @var string
     */
    protected string $delimiter;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param PsrContainerInterface $inner     The container to decorate.
     * @param string                $delimiter The path delimiter.
     */
    public function __construct(PsrContainerInterface $inner, string $delimiter = '/')
    {
        $this->inner = $inner;
        $this->root = '';
        $this->delimiter = $delimiter;
    }

    /**
     * @inheritdoc
     */
    public function get(string $key): mixed
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
     */
    public function has(string $key): bool
    {
        return $this->inner->has($key);
    }
}
