<?php

namespace Dhii\Container\TestHelpers;

use Interop\Container\ServiceProviderInterface;
use PHPUnit\Framework\TestCase;

/**
 * Mock service provider implementation.
 *
 * @since [*next-version*]
 */
class ServiceProviderMock extends AbstractMockHelper implements ServiceProviderInterface
{
    /**
     * @inheritDoc
     *
     * @since [*next-version*]
     */
    public static function nameOfClassToMock() : string
    {
        return ServiceProviderInterface::class;
    }

    /**
     * @inheritDoc
     *
     * @since [*next-version*]
     *
     * @param array $factories
     * @param array $extensions
     *
     * @return static
     */
    public static function create(TestCase $testCase, $factories = [], $extensions = [])
    {
        $instance = parent::create($testCase);

        if ($factories !== null) {
            $instance->mock->method('getFactories')->willReturn($factories);
        }

        if ($extensions !== null) {
            $instance->mock->method('getExtensions')->willReturn($extensions);
        }

        return $instance;
    }

    /**
     * @inheritDoc
     *
     * @since [*next-version*]
     */
    public function getFactories()
    {
        return $this->mock->getFactories();
    }

    /**
     * @inheritDoc
     *
     * @since [*next-version*]
     */
    public function getExtensions()
    {
        return $this->mock->getExtensions();
    }
}
