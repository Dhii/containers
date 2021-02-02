<?php

namespace Dhii\Container\UnitTest;

use Andrew\Proxy;
use Dhii\Container\CompositeCachingServiceProvider;
use Dhii\Container\TestHelpers\ContainerMock;
use Dhii\Container\TestHelpers\InvocableMock;
use Exception;
use PHPUnit\Framework\TestCase;

class CompositeCachingServiceProviderTest extends TestCase
{
    /**
     * Tests whether extensions get correctly merged.
     *
     * Extensions are merged correctly if new extensions are added "inside" old
     * extensions, without overriding them, such that both the old and the
     * new extensions get run, in that order, passing down the result of the old
     * extension to the new.
     *
     * @throws Exception If problem testing.
     */
    public function testMergeExtensions()
    {
        {
            $container = ContainerMock::create($this);
            $prev1 = uniqid('prev1');
            $prev2 = uniqid('prev2');
            $prev3 = uniqid('prev3');
            $prev4 = uniqid('prev3');
            $func1 = InvocableMock::create($this, function () use ($prev1) {
                return $prev1;
            });
            $func2 = InvocableMock::create($this, function () use ($prev2) {
                return $prev2;
            });
            $func3 = InvocableMock::create($this, function () use ($prev3) {
                return $prev3;
            });
            $func4 = InvocableMock::create($this, function () use ($prev4) {
                return $prev4;
            });

            $defaults = [
                'one' => $func1,
                'two' => $func2,
            ];

            $extensions = [
                'two' => $func3,
                'three' => $func4,
            ];

            $subject = new CompositeCachingServiceProvider(['_mergeFactories']);
            $_subject = new Proxy($subject);

            $func1->expectCalled(static::once())->with($container, $prev1);
            $func2->expectCalled(static::once())->with($container, $prev2);
            $func3->expectCalled(static::once())->with($container, $prev2);
            $func4->expectCalled(static::once())->with($container, $prev4);
        }

        {
            $result = $_subject->mergeExtensions($defaults, $extensions);
        }

        {
            // Checking structure of result
            $this->assertCount(3, $result);
            $this->assertArrayHasKey('one', $result);
            $this->assertArrayHasKey('two', $result);
            $this->assertArrayHasKey('three', $result);

            // Checking first (simple) extension
            $this->assertIsCallable($result['one']);
            $this->assertEquals(
                $prev1,
                $result['one']($container, $prev1),
                'Simple extension must return passed value'
            );

            // Checking second (compound) extension
            $this->assertIsCallable($result['two']);
            $this->assertEquals(
                $prev3,
                $result['two']($container, $prev2),
                'Compound extension must return inner-most value'
            );

            // Checking third (simple) extension
            $this->assertIsCallable($result['three']);
            $this->assertEquals(
                $prev4,
                $result['three']($container, $prev4),
                'Simple extension must return passed value'
            );
        }
    }
}
