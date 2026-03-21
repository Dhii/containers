<?php

declare(strict_types=1);

namespace Dhii\Container;

use Dhii\Collection\ContainerInterface;
use Dhii\Container\Exception\NotFoundException;
use Dhii\Container\Util\StringTranslatingTrait;
use Psr\Container\ContainerInterface as PsrContainerInterface;

use function array_key_exists;

/**
 * An implementation of a container that wraps around another to selectively expose or mask certain keys.
 */
class MaskingContainer implements ContainerInterface
{
    use StringTranslatingTrait;

    /**
     * @var PsrContainerInterface
     */
    protected PsrContainerInterface $inner;

    /**
     * @var bool[]
     */
    protected array $mask;

    /**
     * @var bool
     */
    protected bool $defMask;

    /**
     * Constructor.
     *
     * @param PsrContainerInterface $inner       The container whose entries to mask.
     * @param bool                  $defaultMask The default mask. If true, all inner keys are exposed. If false, all
     *                                           inner keys are hidden. Any keys specified in the $mask parameter will
     *                                           naturally override this setting.
     * @param bool[]                $mask        A mapping of keys to booleans, such that `true` exposes the mapped key
     *                                           and `false` hides the mapped key.
     */
    public function __construct(PsrContainerInterface $inner, bool $defaultMask, array $mask)
    {
        $this->inner = $inner;
        $this->defMask = $defaultMask;
        $this->mask = $mask;
    }

    /**
     * @inheritdoc
     */
    #[\Override]
    public function get(string $key): mixed
    {
        if (!$this->isExposed($key)) {
            throw new NotFoundException(
                $this->__('Inner key "%1$s" is not exposed', [$key]),
                0,
                null
            );
        }

        return $this->inner->get($key);
    }

    /**
     * @inheritdoc
     */
    #[\Override]
    public function has(string $key): bool
    {
        return $this->isExposed($key) && $this->inner->has($key);
    }

    /**
     * Checks if a key is exposed through the mask.
     *
     * @param string $key The key to check.
     *
     * @return bool True if the key is exposed, false if the key is hidden.
     */
    protected function isExposed(string $key): bool
    {
        return array_key_exists($key, $this->mask)
            ? $this->mask[$key] !== false
            : $this->defMask;
    }
}
