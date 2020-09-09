<?php

namespace Dhii\Container;

use ArrayIterator;
use Dhii\Collection\WritableContainerInterface;
use Dhii\Collection\WritableMapInterface;
use Dhii\Container\Exception\NotFoundException;
use Dhii\Container\Util\StringTranslatingTrait;
use IteratorAggregate;
use RangeException;

/**
 * A simple mutable dictionary, i.e. an enumerable key-value map.
 */
class Dictionary implements
    IteratorAggregate,
    WritableMapInterface
{
    use StringTranslatingTrait;

    /** @var array */
    protected $data;

    /**
     * @param array $data The key-value map of data.
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * {@inheritDoc}
     */
    public function get($key)
    {
        if (!array_key_exists($key, $this->data)) {
            throw new NotFoundException(
                $this->__('Dictionary does not have key "%1$s"', [$key]),
                0,
                null
            );
        }

        return $this->data[$key];
    }

    /**
     * {@inheritDoc}
     */
    public function has($key)
    {
        $isHas = array_key_exists($key, $this->data);

        return $isHas;
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return new ArrayIterator($this->data);
    }

    /**
     * @inheritDoc
     */
    public function withMappings(array $mappings): WritableContainerInterface
    {
        $dictionary = $this->cloneMe();
        $dictionary->data = $mappings;

        return $dictionary;
    }

    /**
     * @inheritDoc
     */
    public function withAddedMappings(array $mappings): WritableContainerInterface
    {
        $dictionary = $this->cloneMe();
        $dictionary->data = $mappings + $this->data;

        return $dictionary;
    }

    /**
     * @inheritDoc
     */
    public function withoutKeys(array $keys): WritableContainerInterface
    {
        $dictionary = $this->cloneMe();

        foreach ($keys as $i => $key) {
            if (!is_string($key)) {
                throw new RangeException($this->__('Key at index %1$d is not a string', [$i]));
            }
            unset($dictionary->data[$key]);
        }

        return $dictionary;
    }

    /**
     * Creates a copy of this instance
     *
     * @return Dictionary The new instance
     */
    protected function cloneMe(): Dictionary
    {
        return clone $this;
    }
}