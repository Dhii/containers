<?php

namespace Dhii\Container\FuncTest;

use Dhii\Container\PathContainer as TestSubject;
use Dhii\Container\TestHelpers\ComponentMockeryTrait;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use function uniqid;

class PathContainerTest extends TestCase
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
     * Tests that the subject correctly returns intermediate containers when fetching non-existent keys.
     *
     * @since [*next-version*]
     *
     * @throws Exception
     */
    public function testGet()
    {
        $key = 'foo/bar/lorem/ipsum';
        $value = uniqid('value');
        $inner = $this->createContainer([
            $key => $value,
        ]);

        $delimiter = '/';
        $subject = $this->createSubject([$inner, $delimiter]);

        $result1 = $subject->get('foo');
        $this->assertInstanceOf('Psr\Container\ContainerInterface', $result1, 'First result in not a container');

        $result2 = $result1->get('bar/lorem');
        $this->assertInstanceOf('Psr\Container\ContainerInterface', $result2, 'Second result in not a container');

        $result3 = $result2->get('ipsum');
        $this->assertEquals($value, $result3, 'Wrong result value retrieved');
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
        $subject = $this->createSubject([$inner]);

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
        $subject = $this->createSubject([$inner]);

        $key2 = uniqid('key2');
        $expected = $inner->has($key2);
        $result = $subject->has($key2);

        $this->assertEquals($expected, $result, 'Wrong result retrieved');
    }
}
