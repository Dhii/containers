<?php declare(strict_types = 1);

namespace Dhii\Di\FuncTest\Exception;

use Dhii\Container\Exception\NotFoundException;
use Dhii\Container\Exception\NotFoundException as TestSubject;
use Dhii\Container\TestHelpers\ContainerMock;
use Exception;
use PHPUnit\Framework\TestCase;

/**
 * Tests {@see TestSubject}.
 *
 * @package Dhii\Wp\Containers
 */
class NotFoundExceptionTest extends TestCase
{
    /**
     * Tests that the instance is created correctly, and getters work as expected.`
     *
     * @throws Exception If problem testing.
     */
    public function testConstructorAndGetContainerAndDataKey()
    {
        {
            $message = uniqid('message');
            $code = rand(1, 99);
            $prev = new Exception(uniqid('inner-message'));
            $container = ContainerMock::create($this);
            $dataKey = uniqid('data-key');

            $subject = new NotFoundException($message, $code, $prev, $container, $dataKey);
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
