<?php

namespace Dhii\Container\FuncTest;

use Dhii\Container\CompositeCachingServiceProvider;
use Dhii\Container\TestHelpers\ContainerMock;
use Dhii\Container\TestHelpers\ServiceProviderMock;
use Exception;
use PHPUnit\Framework\TestCase;

class CompositeCachingServiceProviderTest extends TestCase
{
    /**
     * Tests that `getFactories()` returns factories correctly overridden.
     *
     * @throws Exception If problem testing.
     */
    public function testGetFactories()
    {
        {
            $srv1 = uniqid('srv1');
            $f1 = function () use ($srv1) { return $srv1; };
            $srv2 = uniqid('srv2');
            $f2 = function () use ($srv2) { return $srv2; };
            $srv3 = uniqid('srv3');
            $f3 = function () use ($srv3) { return $srv3; };
            $srv4 = uniqid('srv4');
            $f4 = function () use ($srv4) { return $srv4; };

            $provider1 = ServiceProviderMock::create($this, [
                'one'       => $f1,
                'two'       => $f2,
            ]);

            $provider2 = ServiceProviderMock::create($this, [
                'two'       => $f3,
                'three'     => $f4,
            ],[]);

            $subject = new CompositeCachingServiceProvider([$provider1, $provider2]);
        }

        {
            $result = $subject->getFactories();
        }

        {
            $this->assertCount(3, $result, 'Wrong number of factories');
            $this->assertArrayHasKey('one', $result, 'Factory missing');
            $this->assertArrayHasKey('two', $result, 'Factory missing');
            $this->assertArrayHasKey('three', $result, 'Factory missing');

            $this->assertEquals($srv1, $result['one'](), 'Wrong factory');
            $this->assertEquals($srv3, $result['two'](), 'Wrong factory');
            $this->assertEquals($srv4, $result['three'](), 'Wrong factory');
        }
    }

    /**
     * Tests that `getExtensions()` returns extensions correctly nested.
     *
     * @throws Exception If problem testing.
     */
    public function testGetExtensions()
    {
        {
            $combination = function (string $a, string $b) {
                return sprintf('%1$s-%2$s', $a, $b);
            };
            $srv1 = uniqid('srv1');
            $f1 = function () use ($srv1) { return $srv1; };
            $srv2 = uniqid('srv2');
            $f2 = function () use ($srv2) { return $srv2; };
            $srv3 = uniqid('srv3');
            $f3 = function ($container, $previous) use ($srv3, $combination) { return $combination($previous, $srv3); };
            $srv4 = uniqid('srv4');
            $f4 = function () use ($srv4) { return $srv4; };

            $provider1 = ServiceProviderMock::create($this, [], [
                'one'       => $f1,
                'two'       => $f2,
            ]);
            $provider2 = ServiceProviderMock::create($this, [], [
                'two'       => $f3,
                'three'     => $f4,
            ]);

            $container = ContainerMock::create($this);

            $subject = new CompositeCachingServiceProvider([$provider1, $provider2]);
        }

        {
            $result = $subject->getExtensions();
        }

        {
            $this->assertCount(3, $result, 'Wrong number of factories');
            $this->assertArrayHasKey('one', $result, 'Factory missing');
            $this->assertArrayHasKey('two', $result, 'Factory missing');
            $this->assertArrayHasKey('three', $result, 'Factory missing');

            $this->assertEquals($srv1, $result['one']($container, $srv1), 'Wrong factory');
            $this->assertEquals($combination($srv2, $srv3), $result['two']($container, $srv2), 'Wrong factory');
            $this->assertEquals($srv4, $result['three']($container, $srv4), 'Wrong factory');
        }
    }
}
