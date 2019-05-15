<?php

namespace Dhii\Container\FuncTest;

use Dhii\Container\CompositeContainer as TestSubject;
use Dhii\Container\TestHelpers\ComponentMockeryTrait;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CompositeContainerTest extends TestCase
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
            ->getMock();
    }

    /**
     * Tests if subject can correctly retrieve a value by key from a list of containers.
     *
     * @throws Exception If problem testing.
     */
    public function testGet()
    {
        {
            $key = uniqid('key');
            $value = uniqid('value');
            $containers = [
                $this->createContainer([

                ]),
                $this->createContainer([
                    uniqid('service1')  => uniqid('value'),
                ]),
                $this->createContainer([
                    uniqid('service2')  => uniqid('value'),
                    $key  => $value,
                    uniqid('service3')  => uniqid('value'),
                ]),
            ];
            $subject = $this->createSubject([$containers]);
        }

        {
            $result = $subject->get($key);
        }

        {
            $this->assertEquals($value, $result, 'Wrong value retrieved');
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
            $containers = [
                $this->createContainer([

                ]),
                $this->createContainer([
                    uniqid('service1')  => uniqid('value'),
                ]),
                $this->createContainer([
                    uniqid('service2')  => uniqid('value'),
                    $key  => uniqid('value'),
                    uniqid('service3')  => uniqid('value'),
                ]),
            ];
            $subject = $this->createSubject([$containers]);
        }

        {
            $result = $subject->has($key);
        }

        {
            $this->assertTrue($result, 'Incorrectly determined having');
        }
    }

    /**
     * Tests that the subject can correctly determine not having a non-existing key.
     *
     * @throws Exception If problem testing.
     */
    public function testHasFalse()
    {
        {
            $containers = [
                $this->createContainer([

                ]),
                $this->createContainer([
                    uniqid('service1')  => uniqid('value'),
                ]),
                $this->createContainer([
                    uniqid('service2')  => uniqid('value'),
                    uniqid('service3')  => uniqid('value'),
                ]),
            ];
            $subject = $this->createSubject([$containers]);
        }

        {
            $result = $subject->has(uniqid('non-existing-service'));
        }

        {
            $this->assertFalse($result, 'Incorrectly determined not having');
        }
    }
}
