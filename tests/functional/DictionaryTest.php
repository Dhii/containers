<?php

namespace Dhii\Container\FuncTest;

use Dhii\Collection\MapInterface;
use Dhii\Container\Dictionary as TestSubject;
use Dhii\Container\TestHelpers\ComponentMockeryTrait;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DictionaryTest extends TestCase
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

    protected function getDictionaryData()
    {
        static $data = null;

        if ($data === null) {
            $data = $this->createArray(
                rand(2, 9),
                function ($index) {
                    return uniqid("value-$index-");
                },
                function ($index) {
                    return uniqid("key-$index-");
                }
            );
        }

        return $data;
    }

    /**
     * Tests that the SUT is what is should be.
     *
     * @return TestSubject|MockObject
     * @throws Exception
     */
    public function testSubject()
    {
        {
            $data = $this->getDictionaryData();
            $subject = $this->createSubject([$data]);
        }

        {
            $this->assertInstanceOf(MapInterface::class, $subject);
        }

        return $subject;
    }

    /**
     * @param TestSubject $subject
     * @depends testSubject
     */
    public function testIteration(TestSubject $subject)
    {
        {
            $data = $this->getDictionaryData();
        }

        {
            $this->assertEquals($data, iterator_to_array($subject));
        }
    }

    /**
     * @param TestSubject $subject
     * @depends testSubject
     */
    public function testAccessors(TestSubject $subject)
    {
        {
            $data = $this->getDictionaryData();
            $key = array_keys($data)[0];
            $value = $data[$key];
        }

        {
            $this->assertTrue($subject->has($key));
            $this->assertEquals($value, $subject->get($key));
        }

        return $subject;
    }

    /**
     * @depends testAccessors
     */
    public function testMutators(TestSubject $subject)
    {
        $subject1 = $subject->withAddedMappings(['one' => 'hello', 'two' => 'world']);
        $this->assertTrue($subject1->has('one'));
        $this->assertEquals('hello', $subject1->get('one'));
        $this->assertTrue($subject1->has('two'));
        $this->assertEquals('world', $subject1->get('two'));

        $subject2 = $subject1->withoutKeys(['one']);
        $this->assertFalse($subject2->has('one'));

        $subject3 = $subject2->withMappings(['three' => 'great']);
        $this->assertTrue($subject3->has('three'));
        $this->assertEquals('great', $subject3->get('three'));
        $this->assertFalse($subject3->has('one'));
        $this->assertFalse($subject3->has('two'));
    }
}
