<?php declare(strict_types = 1);

namespace Dhii\Di\FuncTest\Exception;

use Dhii\Container\Exception\ContainerException;
use Dhii\Container\Exception\ContainerException as TestSubject;
use Dhii\Container\TestHelpers\ContainerMock;
use Exception;
use PHPUnit\Framework\TestCase;

/**
 * Tests {@see TestSubject}.
 *
 * @package Dhii\Wp\Containers
 */
class ContainerExceptionTest extends TestCase
{
    /**
     * Tests that the instance is created correctly, and getters work as expected.`
     *
     * @throws Exception If problem testing.
     */
    public function testConstructorAndGetContainer()
    {
        {
            $message = uniqid('message');
            $code = rand(1, 99);
            $prev = new Exception(uniqid('inner-message'));
            $container = ContainerMock::create($this);

            $subject = new ContainerException($message, $code, $prev, $container);
        }

        {
            try {
                throw $subject;
            } catch (TestSubject $e) {
                $this->assertSame($message, $e->getMessage(), 'Wrong message');
                $this->assertSame($code, $e->getCode(), 'Wrong code');
                $this->assertSame($prev, $e->getPrevious(), 'Wrong previous exception');
            }
        }
    }

}
