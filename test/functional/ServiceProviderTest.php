<?php

namespace Dhii\Container\FuncTest;

use Dhii\Container\ServiceProvider as TestSubject;
use Dhii\Container\TestHelpers\ComponentMockeryTrait;
use Exception;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ServiceProviderTest extends TestCase
{
    use ComponentMockeryTrait;

    /**
     * Creates a new instance of the test subject.
     *
     * @param array $dependencies
     *
     * @return TestSubject The new instance.
     *
     * @throws Exception If problem creating.
     */
    protected function createSubject(array $dependencies)
    {
        $reflection = new ReflectionClass(TestSubject::class);
        $subject = $reflection->newInstanceArgs($dependencies);
        /* @var $subject TestSubject */

        return $subject;
    }

    /**
     * Tests that the extensions passed are correctly retrieved.
     *
     * @throws Exception If problem testing.
     */
    public function testGetExtensions()
    {
        $extensions = [
            'one'           => function () {},
            'two'           => function () {},
        ];
        $subject = $this->createSubject([[], $extensions]);

        $this->assertEquals(
            $extensions,
            $subject->getExtensions(),
            'Wrong extensions retrieved',
            0.0,
            10,
            true
        );
    }

    /**
     * Tests that the factories passed are correctly retrieved.
     *
     * @throws Exception If problem testing.
     */
    public function testGetFactories()
    {
        $factories = [
            'three'           => function () {},
            'four'           => function () {},
        ];
        $subject = $this->createSubject([$factories, []]);

        $this->assertEquals(
            $factories,
            $subject->getFactories(),
            'Wrong factories retrieved',
            0.0,
            10,
            true
        );
    }
}
