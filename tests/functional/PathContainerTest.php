<?php

namespace Dhii\Container\FuncTest;

use Dhii\Container\PathContainer;
use Dhii\Container\TestHelpers\ContainerMock;
use PHPUnit\Framework\TestCase;
use Psr\Container\NotFoundExceptionInterface;

/**
 * @since [*next-version*]
 */
class PathContainerTest extends TestCase
{
    /**
     * @since [*next-version*]
     */
    public function testGet()
    {
        {
            $key1 = 'lorem';
            $key2 = 'ipsum';
            $expected = 'dolor';
        }
        {
            $inner2 = ContainerMock::create($this)->expectHasService($key2, $expected);
            $inner1 = ContainerMock::create($this)->expectHasService($key1, $inner2);
        }
        {
            $delimiter = '/';
            $path = implode($delimiter, [$key1, $key2]);
        }

        $container = new PathContainer($inner1, $delimiter);
        $actual = $container->get($path);

        static::assertEquals($expected, $actual);
    }

    /**
     * @since [*next-version*]
     */
    public function testNotFound()
    {
        $this->expectException(NotFoundExceptionInterface::class);

        $key = 'lorem';
        $inner = ContainerMock::create($this)->expectNotHasService($key);

        $container = new PathContainer($inner);
        $container->get($key);
    }

    /**
     * @since [*next-version*]
     */
    public function testDeepNotFound()
    {
        $this->expectException(NotFoundExceptionInterface::class);

        {
            $key1 = 'lorem';
            $key2 = 'ipsum';
        }
        {
            $inner2 = ContainerMock::create($this)->expectNotHasService($key2);
            $inner1 = ContainerMock::create($this)->expectHasService($key1, $inner2);
        }
        {
            $delimiter = '/';
            $path = implode($delimiter, [$key1, $key2]);
        }

        $container = new PathContainer($inner1, $delimiter);
        $container->get($path);
    }

    /**
     * @since [*next-version*]
     */
    public function testHasTrue()
    {
        {
            $key = 'lorem';
            $value = 'ipsum';
        }
        {
            $inner = ContainerMock::create($this)->expectHasService($key, $value);
        }

        $container = new PathContainer($inner);
        $result = $container->has($key);

        static::assertTrue($result);
    }

    /**
     * @since [*next-version*]
     */
    public function testHasFalse()
    {
        $key = 'lorem';
        $inner = ContainerMock::create($this)->expectNotHasService($key);

        $container = new PathContainer($inner);
        $result = $container->has($key);

        static::assertFalse($result);
    }

    /**
     * @since [*next-version*]
     */
    public function testDeepHasTrue()
    {
        {
            $key1 = 'lorem';
            $key2 = 'ipsum';
            $value = 'dolor';
        }
        {
            $inner2 = ContainerMock::create($this)->expectHasService($key2, $value);
            $inner1 = ContainerMock::create($this)->expectHasService($key1, $inner2);
        }
        {
            $delimiter = '/';
            $path = implode($delimiter, [$key1, $key2]);
        }

        $container = new PathContainer($inner1, $delimiter);
        $result = $container->has($path);

        static::assertTrue($result);
    }

    /**
     * @since [*next-version*]
     */
    public function testDeepHasFalse()
    {
        {
            $key1 = 'lorem';
            $key2 = 'ipsum';
        }
        {
            $inner2 = ContainerMock::create($this)->expectNotHasService($key2);
            $inner1 = ContainerMock::create($this)->expectHasService($key1, $inner2);
        }
        {
            $delimiter = '/';
            $path = implode($delimiter, [$key1, $key2]);
        }

        $container = new PathContainer($inner1, $delimiter);
        $result = $container->has($path);

        static::assertFalse($result);
    }
}
