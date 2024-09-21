<?php

namespace Dhii\Container\TestHelpers;

use PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use PHPUnit\Framework\MockObject\Matcher\Invocation;
use PHPUnit\Framework\MockObject\Matcher\InvokedCount;
use PHPUnit\Framework\TestCase;

/**
 * Mock invocable implementation.
 *
 * @since [*next-version*]
 */
class InvocableMock extends AbstractMockHelper
{
    /**
     * @inheritDoc
     *
     * @since [*next-version*]
     */
    static function nameOfClassToMock() : string
    {
        return static::invocableInterface();
    }

    /**
     * @inheritDoc
     *
     * @since [*next-version*]
     *
     * @param TestCase      $testCase
     * @param callable|null $function
     *
     * @return static
     */
    public static function create(TestCase $testCase, callable $function = null)
    {
        $instance = parent::create($testCase);

        if ($function !== null) {
            $instance->mock->method('__invoke')->willReturnCallback($function);
        }

        return $instance;
    }

    /**
     * @since [*next-version*]
     *
     * @param mixed ...$args
     *
     * @return mixed
     */
    public function __invoke(...$args)
    {
        return $this->mock->__invoke(...$args);
    }

    /**
     * Shorthand for expecting the invocable to be called.
     *
     * @since [*next-version*]
     *
     * @param Invocation|InvokedCount $matcher
     *
     * @return InvocationMocker
     */
    public function expectCalled($matcher) {
        return $this->mock->expects($matcher)->method('__invoke');
    }

    /**
     * Creates the invocable interface that is mocked by this class.
     *
     * @since [*next-version*]
     *
     * @return string
     */
    protected static function invocableInterface() {
        static $className = null;

        if (!$className) {
            $className = uniqid('MockInvocable');
        }

        if (!interface_exists($className)) {
            $class = <<<EOL
interface $className
{
    public function __invoke();
}
EOL;
            eval($class);
        }

        return $className;
    }
}
