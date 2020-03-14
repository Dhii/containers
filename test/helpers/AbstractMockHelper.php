<?php

namespace Dhii\Container\TestHelpers;

use PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use PHPUnit\Framework\MockObject\Matcher\Invocation;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Abstract mock helper class.
 *
 * Extend this class and implement {@link AbstractMockHelper::nameOfClassToMock()} to create a mock class.
 *
 * @since [*next-version*]
 */
abstract class AbstractMockHelper
{
    /**
     * @var MockObject
     */
    protected $mock;

    /**
     * @since [*next-version*]
     *
     * @param TestCase $testCase
     *
     * @return static
     */
    public static function create(TestCase $testCase) {
        $instance = new static();
        $instance->mock = $testCase->getMockBuilder(static::nameOfClassToMock())->getMockForAbstractClass();

        return $instance;
    }

    /**
     * @since [*next-version*]
     *
     * @param Invocation $matcher
     *
     * @return InvocationMocker
     */
    public function expects(Invocation $matcher)
    {
        return $this->mock->expects($matcher);
    }

    /**
     * @since [*next-version*]
     *
     * @param $constraint
     *
     * @return InvocationMocker
     */
    public function method($constraint)
    {
        return $this->mock->method($constraint);
    }

    /**
     * Retrieves the name of the class to mock.
     *
     * @since [*next-version*]
     *
     * @return string
     */
    abstract static function nameOfClassToMock() : string;
}
