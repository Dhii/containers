<?php

namespace Dhii\Di\FuncTest;

use Dhii\Di\ServiceProvider as TestSubject;
use Dhii\Di\TestHelpers\ComponentMockery;
use Exception;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ServiceProviderTest extends TestCase
{
    use ComponentMockery;

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

        $this->assertEqualsCanonicalizing(
            $extensions,
            $subject->getExtensions(),
            'Wrong extensions retrieved'
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

        $this->assertEqualsCanonicalizing(
            $factories,
            $subject->getFactories(),
            'Wrong factories retrieved'
        );
    }
}
