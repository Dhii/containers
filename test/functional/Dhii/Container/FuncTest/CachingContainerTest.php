<?php

namespace Dhii\Container\FuncTest;

use Dhii\Container\CachingContainer as TestSubject;
use Dhii\Container\TestHelpers\ComponentMockeryTrait;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CachingContainerTest extends TestCase
{
    use ComponentMockeryTrait;

    /**
     * Creates a new instance of the test subject.
     *
     * @param array $dependencies A list of constructor args.
     * @param array|null $methods The names of methods to mock in the subject.
     * @return MockObject|TestSubject The new instance.
     * @throws Exception If problem creating.
     */
    protected function createSubject(array $dependencies, array $methods = null)
    {
        return $this->createMockBuilder(TestSubject::class, $methods, $dependencies)
            ->setConstructorArgs($dependencies)
            ->getMock();
    }

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
            $container = $this->createContainer([
                $key       => $value,
            ]);
            $subject = $this->createSubject([$container]);

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

            $container = $this->createContainer([
                $key1       => uniqid('value'),
            ]);
            $container->method('has')->withConsecutive([$key1], [$key2])->willReturnOnConsecutiveCalls(true, false);

            $subject = $this->createSubject([$container]);
        }

        {
            $this->assertTrue($subject->has($key1), 'Wrong determined having');
            $this->assertFalse($subject->has($key2), 'Wrong determined not having');
        }
    }
}
