<?php

namespace Dhii\Container;

use Dhii\Container\Exception\NotFoundException;
use Dhii\Container\Util\StringTranslatingTrait;
use Psr\Container\ContainerInterface;
use function array_key_exists;

/**
 * An implementation of a container that wraps around another to selectively expose or mask certain keys.
 *
 * @since [*next-version*]
 */
class MaskingContainer implements ContainerInterface
{
    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /**
     * @var ContainerInterface
     */
    protected $inner;

    /**
     * @var bool[]
     */
    protected $mask;

    /**
     * @var bool
     */
    protected $defMask;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param ContainerInterface $inner   The container whose entries to mask.
     * @param bool[]             $mask    A mapping of keys to booleans, such that `true` exposes the mapped key and
     *                                    `false` hides the mapped key.
     * @param bool               $default The default mask value to use for keys that are not included in the mask.
     */
    public function __construct(ContainerInterface $inner, array $mask, $default = true)
    {
        $this->inner = $inner;
        $this->mask = $mask;
        $this->defMask = $default;
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function get($key)
    {
        if (!$this->isExposed($key)) {
            throw new NotFoundException(
                $this->__('Key "%1$s" was not found in the inner container or is not exposed', [$key]),
                0,
                null,
                $this,
                $key
            );
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
        return $this->isExposed($key) && $this->inner->has($key);
    }

    /**
     * Checks if a key is exposed through the mask.
     *
     * @since [*next-version*]
     *
     * @param string $key The key to check.
     *
     * @return bool True if the key is exposed, false if the key is hidden.
     */
    protected function isExposed($key)
    {
        return array_key_exists($key, $this->mask)
            ? $this->mask[$key] !== false
            : $this->defMask;
    }
}
