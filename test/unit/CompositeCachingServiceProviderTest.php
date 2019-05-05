<?php

namespace Dhii\Di\UnitTest;

use Dhii\Di\CompositeCachingServiceProvider as TestSubject;
use Dhii\Di\TestHelpers\ComponentMockery;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CompositeCachingServiceProviderTest extends TestCase
{
    use ComponentMockery;

    /**
     * Creates a new instance of the test subject mock.
     *
     * @param array|null $methods The methods to mock.
     * Use `null` to not mock anything. Use empty array to mock everything.
     * @param array|null $dependencies The parameters for the subject constructor.
     * Use `null` to disable the original constructor.
     *
     * @return MockObject|TestSubject
     *
     * @throws Exception If problem creating.
     */
    protected function createSubject(?array $methods = [], ?array $dependencies = null)
    {
        $mock = $this->createMockBuilder(TestSubject::class, $methods, $dependencies)
            ->getMock();

        return $mock;
    }

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
            $container = $this->createContainer([]);
            $prev1 = uniqid('prev1');
            $prev2 = uniqid('prev2');
            $prev3 = uniqid('prev3');
            $prev4 = uniqid('prev3');
            $func1 = $this->createCallable(function () use ($prev1) {
                return $prev1;
            });
            $func2 = $this->createCallable(function () use ($prev2) {
                return $prev2;
            });
            $func3 = $this->createCallable(function () use ($prev3) {
                return $prev3;
            });
            $func4 = $this->createCallable(function () use ($prev4) {
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

            $subject = $this->createSubject(['_mergeFactories']);
            $_subject = $this->proxy($subject);

            $subject->expects($this->exactly(1))
                ->method('_mergeFactories')
                ->willReturnCallback(function ($a, $b) {
                    return array_merge($a, $b);
                });

            $func1->expects($this->exactly(1))
                ->method('__invoke')
                ->with($container, $prev1);
            $func2->expects($this->exactly(1))
                ->method('__invoke')
                ->with($container, $prev2);
            $func3->expects($this->exactly(1))
                ->method('__invoke')
                ->with($container, $prev2);
            $func4->expects($this->exactly(1))
                ->method('__invoke')
                ->with($container, $prev4);

        }

        {
            $result = $_subject->_mergeExtensions($defaults, $extensions);
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
