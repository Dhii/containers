<?php

declare(strict_types=1);

namespace Dhii\Container;

use Dhii\Collection\ContainerInterface;
use Exception;
use Iterator;
use IteratorAggregate;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use Traversable;
use UnexpectedValueException;

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
 * @since [*next-version*]
 * @see   PathContainer For an implementation that achieves the opposite effect.
 */
class SegmentingContainer implements ContainerInterface, Iterator
{
    /**
     * @var PsrContainerInterface
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
     * @var Iterator
     */
    protected $iterator;

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

    public function current()
    {
        return $this->iterator->current();
    }

    public function next(): void
    {
        $this->iterator->next();
    }

    public function key()
    {
        return $this->iterator->key();
    }

    public function valid()
    {
        return $this->startsWith($this->iterator->key(), $this->root);
    }

    public function rewind(): void
    {
        if (!($this->inner instanceof Traversable)) {
            throw new UnexpectedValueException('Cannot rewind: inner container not an iterator');
        }

        $this->iterator = $this->normalizeIterator($this->inner);
        $this->iterator->rewind();
    }

    /**
     * Normalizes a traversable into an iterator.
     *
     * This is helpful because an `Iterator` can be iterated over by explicitly calling known methods,
     * which include those that expose the current key.
     *
     * @param Traversable $traversable The traversable to normalize.
     *
     * @return Iterator The normalized iterator.
     *
     * @throws Exception If problem normalizing.
     */
    protected function normalizeIterator(Traversable $traversable): Iterator
    {
        if ($traversable instanceof Iterator) {
            return $traversable;
        }

        // Any `Traversable` that is not an `Iterator` is an `IteratorAggregate`
        assert($traversable instanceof IteratorAggregate);
        $traversable = $traversable->getIterator();

        return $this->normalizeIterator($traversable);
    }

    /**
     * Determines whether the subject string has another string at the start.
     *
     * @param string $subject The subject to check.
     * @param string $start   The start string to check for.
     *
     * @return bool True if subject starts with specified start string; false otherwise.
     */
    protected function startsWith(string $subject, string $start): bool
    {
        $length = strlen($start);
        return substr($subject, 0, $length) === $start;
    }
}
