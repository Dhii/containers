<?php

namespace Dhii\Container\FuncTest;

use Dhii\Container\DelegatingContainer as TestSubject;
use Dhii\Container\TestHelpers\ComponentMockeryTrait;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class DelegatingContainerTest extends TestCase
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
    protected function createSubject(array $dependencies = [], array $methods = null)
    {
        return $this->createMockBuilder(TestSubject::class, $methods, $dependencies)
            ->getMock();
    }

    /**
     * Tests that the subject is able to retrieve an extended service.
     *
     * The service definition and extension must both receive the parent container.
     *
     * @throws Exception If problem testing.
     */
    public function testGet()
    {
        {
            $serviceName = uniqid('service');
            $parent = $this->createContainer([]);
            $service = 1;
            $definition = $this->createCallable(function (ContainerInterface $container) use ($service) {
                return $service;
            });
            $extension = $this->createCallable(function (ContainerInterface $container, $previous) {
                return $previous + 1;
            });
            $provider = $this->createServiceProvider([
                $serviceName            => $definition,
            ], [
                $serviceName            => $extension,
            ]);
            $subject = $this->createSubject([$provider, $parent]);

            $definition->expects($this->exactly(1))
                ->method('__invoke')
                ->with($parent);
            $extension->expects($this->exactly(1))
                ->method('__invoke')
                ->with($parent, $service);
        }

        {
            $result = $subject->get($serviceName);
        }

        {
            $this->assertEquals(2, $result, 'Wrong result retrieved');
        }
    }

    public function testHasTrue()
    {
        {
            $serviceName = uniqid('service');
            $provider = $this->createServiceProvider([
                $serviceName            => function (ContainerInterface $container) {
                    return 1;
                }
            ], []);
            $subject = $this->createSubject([$provider]);
        }

        {
            $result = $subject->has($serviceName);
        }

        {
            $this->assertTrue($result, 'Wrongly determined having');
        }
    }

    public function testHasFalse()
    {
        {
            $provider = $this->createServiceProvider([], []);
            $subject = $this->createSubject([$provider]);
        }

        {
            $result = $subject->has(uniqid('service'));
        }

        {
            $this->assertFalse($result, 'Wrongly determined not having');
        }
    }
}
