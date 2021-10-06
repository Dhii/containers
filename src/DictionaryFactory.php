<?php

declare(strict_types=1);

namespace Dhii\Container;

use Dhii\Collection\WritableMapFactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * @inheritDoc
 */
class DictionaryFactory implements WritableMapFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function createContainerFromArray(array $data): ContainerInterface
    {
        return new Dictionary($data);
    }
}
