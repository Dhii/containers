<?php declare(strict_types = 1);

namespace Dhii\Container\FuncTest\Exception;

use Dhii\Container\Exception\NotFoundException as TestSubject;
use Dhii\Container\TestHelpers\ComponentMockeryTrait;
use Exception;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Tests {@see TestSubject}.
 *
 * @package Dhii\Wp\Containers
 */
class NotFoundExceptionTest extends TestCase
{
    use ComponentMockeryTrait;

    /**
     * Creates a new instance of the test subject.
     *
     * @param array $dependencies A list of constructor args.
     * @param array<string> $methods The methods to mock.
     * @return MockObject|TestSubject The new instance.
     * @throws Exception If problem creating.
     */
    protected function createSubject(array $dependencies, array $methods = [])
    {
        return $this->createMockBuilder(TestSubject::class, $methods, $dependencies)
            ->getMock();
    }

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

            $subject = $this->createSubject([$message, $code, $prev]);
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
