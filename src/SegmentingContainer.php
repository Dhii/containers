<?php

namespace Dhii\Container;

use Psr\Container\ContainerInterface;
use function array_filter;
use function ltrim;

/**
 * A container implementation that wraps around another to allow the container to be segmented into sub-containers.
 *
 * Segmenting containers are intended to be used with hierarchical keys, i.e. keys that use a delimiter to indicate
 * segments in the key, such as "some/test/key" or "some.deep.config.value".
 *
 * A segmenting container can yield 2 different kinds of results:
 * * values
 * * "segments"
 *
 * When `get()` is called, the key is split into segments according to the delimiter, and those segments are appended
 * to an internal segment list (more on this further below) to obtain the full key.
 *
 * If the full key corresponds to a value in the inner container, the value for that key is returned.
 *
 * Otherwise, a new segmenting container is created and returned. This segmenting container will store the full key
 * that resulted in its creation as an internal segment list, which will be automatically prepended to all keys that
 * are requested through its `get()` method.
 *
 * Example usage:
 *      Consider the hierarchy:
 *          [
 *              "config" => [
 *                  "db" => [
 *                      "host" => "localhost",
 *                      "port" => 3306
 *                  ]
 *              ]
 *          ]
 *
 *      The segmenting container can create a container that directly provides the "host" and "port":
 *          $config = new SegmentingContainer($c, '.');
 *          $dbConfig = $config->get('config.db');
 *          $dbConfig->get("host"); // "localhost"
 *          $dbConfig->get("port"); // 3306
 *
 * @since [*next-version*]
 */
class SegmentingContainer implements ContainerInterface
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
