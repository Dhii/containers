<?php

namespace Dhii\Container\FuncTest;

use Dhii\Container\DelegatingContainer;
use Dhii\Container\TestHelpers\ContainerMock;
use Dhii\Container\TestHelpers\InvocableMock;
use Dhii\Container\TestHelpers\ServiceProviderMock;
use Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

class DelegatingContainerTest extends TestCase
{
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
            $parent = ContainerMock::create($this);
            $service = 1;
            $definition = InvocableMock::create($this, function (ContainerInterface $container) use ($service) {
                return $service;
            });
            $extension = InvocableMock::create($this, function (ContainerInterface $container, $previous) {
                return $previous + 1;
            });
            $provider = ServiceProviderMock::create($this, [
                $serviceName            => $definition,
            ], [
                $serviceName            => $extension,
            ]);
            $subject = new DelegatingContainer($provider, $parent);

            $definition->expectCalled(static::once())->with($parent);
            $extension->expectCalled(static::once())->with($parent, $service);
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
            $provider = ServiceProviderMock::create($this, [
                $serviceName            => function (ContainerInterface $container) {
                    return 1;
                }
            ], []);
            $subject = new DelegatingContainer($provider);
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
            $provider = ServiceProviderMock::create($this, [], []);
            $subject = new DelegatingContainer($provider);
        }

        {
            $result = $subject->has(uniqid('service'));
        }

        {
            $this->assertFalse($result, 'Wrongly determined not having');
        }
    }

    public function testRecursiveDetection()
    {
        {
            $provider = ServiceProviderMock::create($this, [
                'a' => function (ContainerInterface $c) {
                    return $c->get('b');
                },
                'b' => function (ContainerInterface $c) {
                    return $c->get('c');
                },
                'c' => function (ContainerInterface $c) {
                    return $c->get('a');
                },
            ]);

            $subject = new DelegatingContainer($provider);
        }
        {
            try {
                $subject->get('a');
                $this->fail('Expected exception to be thrown');
            } catch (ContainerExceptionInterface $exception) {
                $this->assertStringContainsString(
                    'a -> b -> c -> a',
                    $exception->getMessage(),
                    'Exception message does not properly report circular dependency'
                );
            }
        }
    }
}
