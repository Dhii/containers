<?php

namespace Dhii\Container\FuncTest;

use Dhii\Container\CompositeContainer;
use Dhii\Container\TestHelpers\ContainerMock;
use Exception;
use PHPUnit\Framework\TestCase;

class CompositeContainerTest extends TestCase
{
    /**
     * Tests if subject can correctly retrieve a value by key from a list of containers.
     *
     * @throws Exception If problem testing.
     */
    public function testGet()
    {
        {
            $key = uniqid('key');
            $value1 = uniqid('value1');
            $value2 = uniqid('value2');
        }

        {
            $c1 = ContainerMock::create($this);
            $c1->expectNotHasService($key);

            $c2 = ContainerMock::create($this);
            $c2->expectHasService($key, $value1);

            $c3 = ContainerMock::create($this);
            $c3->expectHasService($key, $value2);

            $subject = new CompositeContainer([$c1, $c2, $c3]);
        }

        {
            $result = $subject->get($key);
        }

        {
            $this->assertEquals($value1, $result, 'Wrong value retrieved');
        }
    }

    /**
     * Tests that the subject can correctly determine having an existing key.
     *
     * @throws Exception If problem testing.
     */
    public function testHasTrue()
    {
        {
            $key = uniqid('key');
            $value1 = uniqid('value1');
            $value2 = uniqid('value2');
        }

        {
            $c1 = ContainerMock::create($this);
            $c1->expectNotHasService($key);

            $c2 = ContainerMock::create($this);
            $c2->expectHasService($key, $value1);

            $c3 = ContainerMock::create($this);
            $c3->expectHasService($key, $value2);

            $subject = new CompositeContainer([$c1, $c2, $c3]);
        }

        {
            $result = $subject->has($key);
        }

        {
            $this->assertTrue($result, 'Wrong value retrieved');
        }
    }

    /**
     * Tests that the subject can correctly determine not having a non-existing key.
     *
     * @throws Exception If problem testing.
     */
    public function testHasFalse()
    {
        $key = uniqid('key');

        {
            $c1 = ContainerMock::create($this);
            $c1->expectNotHasService($key);

            $c2 = ContainerMock::create($this);
            $c2->expectNotHasService($key);

            $c3 = ContainerMock::create($this);
            $c3->expectNotHasService($key);

            $subject = new CompositeContainer([$c1, $c2, $c3]);
        }

        {
            $result = $subject->has($key);
        }

        {
            $this->assertFalse($result, 'Incorrectly determined not having');
        }
    }
}
