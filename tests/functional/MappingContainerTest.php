<?php

namespace Dhii\Container\FuncTest;

use Dhii\Container\MappingContainer;
use Dhii\Container\TestHelpers\ContainerMock;
use Psr\Container\NotFoundExceptionInterface;
use Exception;
use PHPUnit\Framework\TestCase;
use function uniqid;

class MappingContainerTest extends TestCase
{
    /**
     * Tests that the subject correctly invokes the callback with the inner container's result, and returns the
     * callback's result.
     *
     * @since [*next-version*]
     *
     * @throws Exception
     */
    public function testGet()
    {
        $key = uniqid('key');
        $value = uniqid('value');
        $inner = ContainerMock::create($this)->expectHasService($key, $value);

        $newValue = uniqid('new-value');
        $cc = null; // This will record the container that was given as the $c arg in the callback
        $callback = function ($v, $k, $c) use ($value, $key, &$cc, $newValue) {
            $this->assertEquals($v, $value, 'Wrong value');
            $this->assertEquals($k, $key, 'Wrong key');

            $cc = $c;

            return $newValue;
        };

        $subject = new MappingContainer($inner, $callback);

        $result = $subject->get($key);

        $this->assertEquals($newValue, $result, 'Wrong result retrieved');
        $this->assertSame($subject, $cc, 'Wrong container passed as 3rd arg to callback');
    }

    /**
     * Tests that the subject correctly throws when the inner container does not have an entry, skipping the
     * callback's invocation.
     *
     * @since [*next-version*]
     *
     * @throws Exception
     */
    public function testGetNotFound()
    {
        $key = uniqid('key');
        $inner = ContainerMock::create($this)->expectNotHasService($key);

        $callback = function () {
            $this->fail('Callback should not have been invoked');
        };

        $subject = new MappingContainer($inner, $callback);

        try {
            $subject->get($key);

            $this->fail('Container did not throw a NotFoundExceptionInterface');
        } catch (Exception $exception) {
            $this->assertInstanceOf(
                NotFoundExceptionInterface::class,
                $exception,
                'Exception does not implement correct interface'
            );
        }
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

        $callback = function () {
            $this->fail('Callback should not have been invoked');
        };
        $subject = new MappingContainer($inner, $callback);

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

        $callback = function ($v) {
            $this->fail('Callback should not have been invoked');
        };
        $subject = new MappingContainer($inner, $callback);

        $expected = $inner->has($key);
        $result = $subject->has($key);

        $this->assertEquals($expected, $result, 'Wrong result retrieved');
    }
}
