<?php

namespace Dhii\Container\TestHelpers;

use Dhii\Container\Exception\NotFoundException;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Just a class that has methods for the sake of having them.
 */
class MethodClass
{
    public static function staticMethod(): string
    {
        return __METHOD__;
    }

    public function instanceMethod(): string
    {
        return __METHOD__;
    }
}
