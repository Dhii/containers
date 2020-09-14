<?php

namespace Dhii\Container\FuncTest;

use Dhii\Container\SegmentingContainer;
use Dhii\Container\TestHelpers\ContainerMock;
use Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use function uniqid;

class SegmentingContainerTest extends TestCase
{
    /**
     * Tests that the subject correctly returns intermediate containers when fetching non-existent keys.
     *
     * @since [*next-version*]
     *
     * @throws Exception
     */
    public function testGet()
    {
        $key = 'foo';
        $value = uniqid('value');
        $inner = ContainerMock::create($this)->expectHasService($key, $value);

        $delimiter = '/';
        $subject = new SegmentingContainer($inner, $delimiter);

        $result = $subject->get('foo');
        $this->assertEquals($value, $result);
    }

    /**
     * Tests that the subject's results are containers that can themselves also return more containers.
     *
     * @since [*next-version*]
     *
     * @throws Exception
     */
    public function testGetDeep()
    {
        {
            $delimiter = '/';
            $path = [
                'lorem',
                'ipsum',
                'dolor',
            ];
        }
        {
            $key1 = $path[0];
            $key2 = $key1 . $delimiter . $path[1];
            $key3 = $key2 . $delimiter . $path[2];
            $value = uniqid('value');
        }

        {
            $inner = ContainerMock::create($this);
            // Container only returns true for full path
            $inner->method('has')
                  ->withConsecutive([$key1], [$key2], [$key3])
                  ->willReturnOnConsecutiveCalls(false, false, true);
            // Container returns value for full path
            $inner->method('get')->with($key3)->willReturn($value);
        }

        $subject = new SegmentingContainer($inner, $delimiter);

        $c1 = $subject->get('lorem');
        $this->assertInstanceOf(ContainerInterface::class, $c1);

        $c2 = $c1->get('ipsum');
        $this->assertInstanceOf(ContainerInterface::class, $c2);

        $result3 = $c2->get('dolor');
        $this->assertEquals($value, $result3, 'Wrong result value retrieved');
    }

    /**
     * Tests that the subject can accept path-like keys to return deeper container.
     *
     * @since [*next-version*]
     *
     * @throws Exception
     */
    public function testGetPath()
    {
        {
            $delimiter = '/';
            $path = [
                'lorem',
                'ipsum',
                'dolor',
            ];
        }
        {
            $key1 = $path[0] . $delimiter . $path[1];
            $key2 = $key1 . $delimiter . $path[2];
            $value = uniqid('value');
        }
        {
            $inner = ContainerMock::create($this);
            // Container only returns true for full path
            $inner->method('has')
                  ->withConsecutive([$key1], [$key2])
                  ->willReturnOnConsecutiveCalls(false, true);
            // Container returns value for full path
            $inner->method('get')->with($key2)->willReturn($value);
        }

        $delimiter = '/';
        $subject = new SegmentingContainer($inner, $delimiter);

        $c1 = $subject->get('lorem/ipsum');
        $this->assertInstanceOf(ContainerInterface::class, $c1);

        $result = $c1->get('dolor');
        $this->assertEquals($value, $result, 'Wrong result value retrieved');
    }

    /**
     * Tests that the subject correctly reports whether it has a key or not in the same way the inner container does.
     *
     * @since [*next-version*]
     *
     * @throws Exception
     */
    public function testHasTrue()
    {
        $key = uniqid('key');
        $value = uniqid('value');
        $inner = ContainerMock::create($this)->expectHasService($key, $value);

        $subject = new SegmentingContainer($inner);

        $expected = $inner->has($key);
        $result = $subject->has($key);

        $this->assertEquals($expected, $result, 'Wrong result retrieved');
    }

    /**
     * Tests that the subject correctly reports whether it has a key or not in the same way the inner container does.
     *
     * @since [*next-version*]
     *
     * @throws Exception
     */
    public function testHasFalse()
    {
        $key = uniqid('key');
        $inner = ContainerMock::create($this)->expectNotHasService($key);

        $subject = new SegmentingContainer($inner);

        $expected = $inner->has($key);
        $result = $subject->has($key);

        $this->assertEquals($expected, $result, 'Wrong result retrieved');
    }
}
