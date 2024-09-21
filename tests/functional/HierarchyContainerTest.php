<?php

namespace Dhii\Container\FuncTest;

use Dhii\Container\HierarchyContainer;
use Psr\Container\NotFoundExceptionInterface;
use PHPUnit\Framework\TestCase;

/**
 * @since [*next-version*]
 */
class HierarchyContainerTest extends TestCase
{
    /**
     * @since [*next-version*]
     */
    public function testCreateSubContainer()
    {
        $container = new HierarchyContainer([
            'config' => []
        ]);

        $result = $container->get('config');

        static::assertInstanceOf(HierarchyContainer::class, $result);
    }

    /**
     * @since [*next-version*]
     */
    public function testGetValue()
    {
        $container = new HierarchyContainer([
            'config' => ($expected = uniqid()),
        ]);

        $actual = $container->get('config');

        static::assertEquals($expected, $actual);
    }

    /**
     * @since [*next-version*]
     */
    public function testDeepGet()
    {
        $container = new HierarchyContainer([
            'config' => [
                'db' => [
                    'host' => ($expected = uniqid()),
                ],
            ],
        ]);

        $result = $container->get('config')->get('db')->get('host');

        static::assertEquals($expected, $result);
    }

    /**
     * @since [*next-version*]
     */
    public function testNotFound()
    {
        $this->expectException(NotFoundExceptionInterface::class);

        $container = new HierarchyContainer([
            'key' => 'value',
        ]);

        $container->get('not_exists');
    }

    /**
     * @since [*next-version*]
     */
    public function testDeepNotFound()
    {
        $this->expectException(NotFoundExceptionInterface::class);

        $container = new HierarchyContainer([
            'config' => [
                'db' => [
                    'host' => 'localhost',
                    'port' => 3306,
                ],
            ],
        ]);

        $container->get('config')->get('db')->get('not_exists');
    }

    /**
     * @since [*next-version*]
     */
    public function testHasTrue()
    {
        $container = new HierarchyContainer([
            'key' => 'value',
        ]);

        $result = $container->has('key');

        static::assertTrue($result);
    }

    /**
     * @since [*next-version*]
     */
    public function testHasFalse()
    {
        $container = new HierarchyContainer([
            'key' => 'value',
        ]);

        $result = $container->has('not_exists');

        static::assertFalse($result);
    }

    /**
     * @since [*next-version*]
     */
    public function testDeepHasTrue()
    {
        $container = new HierarchyContainer([
            'config' => [
                'db' => [
                    'host' => 'localhost',
                    'port' => 3306
                ]
            ],
        ]);

        $result = $container->get('config')->get('db')->has('port');

        static::assertTrue($result);
    }

    /**
     * @since [*next-version*]
     */
    public function testDeepHasFalse()
    {
        $container = new HierarchyContainer([
            'config' => [
                'db' => [
                    'host' => 'localhost',
                    'port' => 3306
                ]
            ],
        ]);

        $result = $container->get('config')->get('db')->has('not_exists');

        static::assertFalse($result);
    }
}
