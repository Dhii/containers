<?php

namespace Dhii\Container\FuncTest;

use Dhii\Container\CachingContainer;
use Dhii\Container\TestHelpers\ContainerMock;
use Exception;
use PHPUnit\Framework\TestCase;

class CachingContainerTest extends TestCase
{
    /**
     * Tests that the subject can correctly retrieve a value from the inner container.
     *
     * The value must be the _same thing_ no matter how many times it is retrieved.
     * The subject must only ask the inner container once for the value.
     *
     * @throws Exception If problem testing.
     */
    public function testGet()
    {
        {
            $key = uniqid('key');
            $value = (object) [];
            $container = ContainerMock::create($this)->expectHasService($key, $value);
            $subject = new CachingContainer($container);

            $container->expects($this->exactly(1))
                ->method('get')
                ->with($key);
        }

        {
            $operations = rand(2, 9);
            $result = null;

            for ($i = 0; $i < $operations; $i++) {
                $result = $subject->get($key);
            }
        }

        {
            $this->assertSame($value, $result, 'Wrong value retrieved');
        }
    }

    /**
     * Tests that the subject correctly determines having keys.
     *
     * Subject must report having a key only if the inner container has that key.
     *
     * @depends testGet
     */
    public function testHas()
    {
        {
            $key1 = uniqid('key');
            $key2 = uniqid('not-exists');

            $container = ContainerMock::create($this);
            $container->method('has')->withConsecutive([$key1], [$key2])->willReturnOnConsecutiveCalls(true, false);

            $subject = new CachingContainer($container);
        }

        {
            $this->assertTrue($subject->has($key1), 'Wrong determined having');
            $this->assertFalse($subject->has($key2), 'Wrong determined not having');
        }
    }
}
