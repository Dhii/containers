<?php

namespace Dhii\Container;

use Dhii\Container\Util\StringTranslatingTrait;
use Psr\Container\ContainerInterface;
use function call_user_func_array;

/**
 * A container implementation that decorates another container, invoking a callback for possible modifications to the
 * decorated container's values.
 */
class CallbackContainer implements ContainerInterface
{
    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /**
     * @since [*next-version*]
     *
     * @var callable
     */
    protected $callback;

    /**
     * @since [*next-version*]
     *
     * @var ContainerInterface
     */
    protected $inner;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param ContainerInterface|null $inner      The container instance to decorate.
     * @param callable                $callback   The callback to invoke on get. It will be passed 3 parameters:
     *                                            * The inner container's value for the key being fetched.
     *                                            * The key being fetched.
     *                                            * A reference to this container instance.
     */
    public function __construct(ContainerInterface $inner, callable $callback)
    {
        $this->callback = $callback;
        $this->inner = $inner;
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function get($key)
    {
        return call_user_func_array($this->callback, [$this->inner->get($key), $key, $this]);
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
