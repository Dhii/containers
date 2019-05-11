<?php

namespace Dhii\Container\FuncTest;

use Dhii\Container\PrefixingContainer as TestSubject;
use Dhii\Container\TestHelpers\ComponentMockeryTrait;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use function uniqid;

class PrefixingContainerTest extends TestCase
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
     * Tests that the subject is able to delegate retrieval to the inner container with the un-prefixed key when
     * strict mode is enabled.
     *
     * @throws Exception If problem testing.
     */
    public function testGetStrict()
    {
        $serviceKey = uniqid('service-key');
        $serviceVal = uniqid('service-val');

        $prefix = uniqid('prefix');
        $innerKey = $serviceKey;
        $outerKey = $prefix . $serviceKey;

        $inner = $this->createContainer([
            $innerKey => $serviceVal
        ]);

        $strict = true;
        $subject = $this->createSubject([$inner, $prefix, $strict]);

        $result = $subject->get($outerKey);

        $this->assertSame($serviceVal, $result, 'Wrong result retrieved');
    }

    /**
     * Tests that the subject is able to delegate retrieval to the inner container with the un-prefixed key when
     * strict mode is disabled.
     *
     * @throws Exception If problem testing.
     */
    public function testGetNonStrict()
    {
        $serviceKey = uniqid('service-key');
        $serviceVal = uniqid('service-val');

        $prefix = uniqid('prefix');
        $innerKey = $prefix . $serviceKey;
        $outerKey = $prefix . $serviceKey;

        $inner = $this->createContainer([
            $innerKey => $serviceVal
        ]);

        $strict = false;
        $subject = $this->createSubject([$inner, $prefix, $strict]);

        $result = $subject->get($outerKey);

        $this->assertSame($serviceVal, $result, 'Wrong result retrieved');
    }

    /**
     * Tests that the subject is able to delegate checking for the un-prefixed service key to the inner container
     * when strict mode is enabled.
     *
     * @throws Exception If problem testing.
     */
    public function testHasStrict()
    {
        $serviceKey = uniqid('service-key');
        $serviceVal = uniqid('service-val');

        $prefix = uniqid('prefix');
        $innerKey = $serviceKey;
        $outerKey = $prefix . $serviceKey;

        $inner = $this->createContainer([
            $innerKey => $serviceVal
        ]);

        $strict = true;
        $subject = $this->createSubject([$inner, $prefix, $strict]);

        $result = $subject->has($outerKey);

        $this->assertEquals(true, $result, 'Wrong result retrieved');
    }

    /**
     * Tests that the subject is unable to find a prefixed entry when strict mode is enabled.
     *
     * @throws Exception If problem testing.
     */
    public function testHasStrictFalse()
    {
        $serviceKey = uniqid('service-key');
        $serviceVal = uniqid('service-val');

        $prefix = uniqid('prefix');
        $innerKey = $prefix . $serviceKey;
        $outerKey = $prefix . $serviceKey;

        $inner = $this->createContainer([
            $innerKey => $serviceVal
        ]);

        $strict = true;
        $subject = $this->createSubject([$inner, $prefix, $strict]);

        $result = $subject->has($outerKey);

        $this->assertEquals(false, $result, 'Wrong result retrieved');
    }

    /**
     * Tests that the subject is able to delegate lookup to the inner container for the prefixed key when strict mode
     * is disabled.
     *
     * @throws Exception If problem testing.
     */
    public function testHasNonStrict()
    {
        $serviceKey = uniqid('service-key');
        $serviceVal = uniqid('service-val');

        $prefix = uniqid('prefix');
        $innerKey = $prefix . $serviceKey;
        $outerKey = $prefix . $serviceKey;

        $inner = $this->createContainer([
            $innerKey => $serviceVal
        ]);

        $strict = false;
        $subject = $this->createSubject([$inner, $prefix, $strict]);

        $result = $subject->has($outerKey);

        $this->assertEquals(true, $result, 'Wrong result retrieved');
    }

    /**
     * Tests that the subject is unable to find a non-existing service, regardless of prefix and strict mode.
     *
     * @throws Exception If problem testing.
     */
    public function testHasFalse()
    {
        $serviceKey = uniqid('service-key');
        $serviceVal = uniqid('service-val');

        $prefix = uniqid('prefix');
        $innerKey = uniqid('another-key');
        $outerKey = $prefix . $serviceKey;

        $inner = $this->createContainer([
            $innerKey => $serviceVal
        ]);

        $strict = false;
        $subject = $this->createSubject([$inner, $prefix, $strict]);

        $result = $subject->has($outerKey);

        $this->assertEquals(false, $result, 'Wrong result retrieved');
    }
}
