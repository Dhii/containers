<?php

namespace Dhii\Container\FuncTest;

use Dhii\Container\CallbackContainer as TestSubject;
use Dhii\Container\TestHelpers\ComponentMockeryTrait;
use Dhii\Data\Container\Exception\NotFoundExceptionInterface;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use function uniqid;

class CallbackContainerTest extends TestCase
{
    use ComponentMockeryTrait;

    /**
     * Creates a new instance of the test subject.
     *
     * @param array      $dependencies A list of constructor args.
     * @param array|null $methods      The names of methods to mock in the subject.
     *
     * @return MockObject|TestSubject The new instance.
     * @throws Exception If problem creating.
     */
    protected function createSubject(array $dependencies = [], array $methods = null)
    {
        return $this->createMockBuilder(TestSubject::class, $methods, $dependencies)
                    ->getMock();
    }

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
        $inner = $this->createContainer([
            $key => $value,
        ]);

        $newValue = uniqid('new-value');
        $cc = null; // This will record the container that was given as the $c arg in the callback
        $callback = function ($v, $k, $c) use ($value, $key, &$cc, $newValue) {
            $this->assertEquals($v, $value, 'Wrong value');
            $this->assertEquals($k, $key, 'Wrong key');

            $cc = $c;

            return $newValue;
        };

        $subject = $this->createSubject([$inner, $callback]);

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
        $inner = $this->createContainer([]);
        $callback = function () {
            $this->fail('Callback should not have been invoked');
        };

        $subject = $this->createSubject([$inner, $callback]);
        $key = uniqid('key');

        try {
            $subject->get($key);

            $this->fail('Container did not throw a NotFoundExceptionInterface');
        } catch (NotFoundExceptionInterface $exception) {
            $this->assertEquals($key, $exception->getDataKey(), 'Wrong exception data key');
            $this->assertSame($inner, $exception->getContainer(), 'Wrong exception container');
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
        $inner = $this->createContainer([
            $key => $value,
        ]);

        $callback = function ($v) {
            $this->fail('Callback should not have been invoked');
        };
        $subject = $this->createSubject([$inner, $callback]);

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
        $value = uniqid('value');
        $inner = $this->createContainer([
            $key => $value,
        ]);

        $callback = function ($v) {
            $this->fail('Callback should not have been invoked');
        };
        $subject = $this->createSubject([$inner, $callback]);

        $key2 = uniqid('key2');
        $expected = $inner->has($key2);
        $result = $subject->has($key2);

        $this->assertEquals($expected, $result, 'Wrong result retrieved');
    }
}
