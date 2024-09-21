<?php

declare(strict_types=1);

namespace Dhii\Container;

use Dhii\Collection\WritableMapFactoryInterface;
use Dhii\Collection\WritableMapInterface;

/**
 * @inheritDoc
 */
class DictionaryFactory implements WritableMapFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function createContainerFromArray(array $data): WritableMapInterface
    {
        return new Dictionary($data);
    }
}
