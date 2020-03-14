<?php

namespace Dhii\Container\TestHelpers;

use Dhii\Container\Exception\NotFoundException;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Mock container implementation.
 *
 * @since [*next-version*]
 */
class ContainerMock extends AbstractMockHelper implements ContainerInterface
{
    /**
     * Index of current expectation.
     *
     * @var int
     */
    protected $_expIdx = 0;

    /**
     * @inheritDoc
     *
     * @since [*next-version*]
     */
    public static function nameOfClassToMock() : string
    {
        return ContainerInterface::class;
    }

    /**
     * @inheritDoc
     *
     * @since [*next-version*]
     */
    public function get($id)
    {
        return $this->mock->get($id);
    }

    /**
     * @inheritDoc
     *
     * @since [*next-version*]
     */
    public function has($id)
    {
        return $this->mock->has($id);
    }

    /**
     * Shorthand for expecting a service from the mock container.
     *
     * @since [*next-version*]
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return static
     */
    public function expectHasService($key, $value)
    {
        $this->mock->method('get')
                   ->with($key)
                   ->willReturn($value);

        $this->mock->method('has')
                   ->with($key)
                   ->willReturn(true);

        return $this;
    }

    /**
     * Shorthand for expecting a service to not exist in the mock container.
     *
     * @since [*next-version*]
     *
     * @param string $key
     *
     * @return static
     */
    public function expectNotHasService($key)
    {
        $this->mock->method('get')
                   ->with($key)
                   ->willThrowException(new NotFoundException("", 0, null, $this, $key));

        $this->mock->method('has')
                   ->with($key)
                   ->willReturn(false);

        return $this;
    }

    public function expectGet(array $expectations)
    {
        $keys = array_keys($expectations);
        $values = array_values($expectations);

        $args = array_map(function ($key) {
            return [$key];
        }, $keys);

        $this->mock->method('get')
                   ->withConsecutive(...$args)
                   ->willReturnCallback(function ($arg) use ($keys, $values) {
                       static $idx = -1;
                       $idx++;

                       $key = $keys[$idx];
                       $val = $values[$idx];

                       if ($arg !== $key) {
                           TestCase::fail(
                               "Parameter 0 for invocation #${idx} ContainerInterface::get('{$arg}') does not match expected value '${key}'"
                           );

                           return null;
                       }

                       if ($val instanceof NotFoundExceptionInterface) {
                           throw new NotFoundException(
                               $val->getMessage(), $val->getCode(), $val->getPrevious(), $this, $arg
                           );
                       }

                       return $val;
                   });

        $this->mock->method('has')
                   ->withConsecutive(...$args)
                   ->willReturnCallback(function ($arg) use ($keys, $values) {
                       static $idx = -1;
                       $idx++;

                       $key = $keys[$idx];
                       $val = $values[$idx];

                       if ($arg !== $key) {
                           TestCase::fail(
                               "Parameter 0 for invocation #${idx} ContainerInterface::has('{$arg}') does not match expected value '${key}'"
                           );

                           return null;
                       }

                       return !($val instanceof NotFoundExceptionInterface);
                   });

        return $this;
    }
}
