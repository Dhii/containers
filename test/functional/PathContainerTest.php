<?php

namespace Dhii\Container\FuncTest;

use Dhii\Container\Exception\NotFoundException;
use Dhii\Container\PathContainer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;

/**
 * @since [*next-version*]
 */
class PathContainerTest extends TestCase
{
    /**
     * @since [*next-version*]
     * @return MockObject|ContainerInterface
     * @throws ReflectionException
     */
    protected function createMockContainer()
    {
        return $this->getMockForAbstractClass(ContainerInterface::class);
    }

    /**
     * @since [*next-version*]
     * @throws ReflectionException
     */
    public function testGet()
    {
        {
            $key1 = 'lorem';
            $key2 = 'ipsum';
            $expected = 'dolor';
        }
        {
            $inner2 = $this->createMockContainer();
            $inner2->expects(static::once())->method('get')->with($key2)->willReturn($expected);

            $inner1 = $this->createMockContainer();
            $inner1->expects(static::once())->method('get')->with($key1)->willReturn($inner2);
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
     * @throws ReflectionException
     */
    public function testNotFound()
    {
        $this->expectException(NotFoundExceptionInterface::class);

        {
            $key = 'lorem';
        }
        {
            $inner = $this->createMockContainer();
            $inner->expects(static::once())->method('get')->with($key)->willThrowException(new NotFoundException());
        }

        $container = new PathContainer($inner);
        $container->get($key);
    }

    /**
     * @since [*next-version*]
     * @throws ReflectionException
     */
    public function testDeepNotFound()
    {
        $this->expectException(NotFoundExceptionInterface::class);

        {
            $key1 = 'lorem';
            $key2 = 'ipsum';
        }
        {
            $inner2 = $this->createMockContainer();
            $inner2->expects(static::once())->method('get')->with($key2)->willThrowException(new NotFoundException());

            $inner1 = $this->createMockContainer();
            $inner1->expects(static::once())->method('get')->with($key1)->willReturn($inner2);
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
     * @throws ReflectionException
     */
    public function testHasTrue()
    {
        {
            $key = 'lorem';
            $value = 'ipsum';
        }
        {
            $inner = $this->createMockContainer();
            $inner->expects(static::once())->method('get')->with($key)->willReturn($value);
        }

        $container = new PathContainer($inner);
        $result = $container->has($key);

        static::assertTrue($result);
    }

    /**
     * @since [*next-version*]
     * @throws ReflectionException
     */
    public function testHasFalse()
    {
        {
            $key = 'lorem';
        }
        {
            $inner = $this->createMockContainer();
            $inner->expects(static::once())->method('get')->with($key)->willThrowException(new NotFoundException());
        }

        $container = new PathContainer($inner);
        $result = $container->has($key);

        static::assertFalse($result);
    }

    /**
     * @since [*next-version*]
     * @throws ReflectionException
     */
    public function testDeepHasTrue()
    {
        {
            $key1 = 'lorem';
            $key2 = 'ipsum';
            $value = 'dolor';
        }
        {
            $inner2 = $this->createMockContainer();
            $inner2->expects(static::once())->method('get')->with($key2)->willReturn($value);

            $inner1 = $this->createMockContainer();
            $inner1->expects(static::once())->method('get')->with($key1)->willReturn($inner2);
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
     * @throws ReflectionException
     */
    public function testDeepHasFalse()
    {
        {
            $key1 = 'lorem';
            $key2 = 'ipsum';
        }
        {
            $inner2 = $this->createMockContainer();
            $inner2->expects(static::once())->method('get')->with($key2)->willThrowException(new NotFoundException());

            $inner1 = $this->createMockContainer();
            $inner1->expects(static::once())->method('get')->with($key1)->willReturn($inner2);
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
