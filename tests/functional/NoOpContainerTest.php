<?php

namespace Dhii\Container\FuncTest;

use Dhii\Container\NoOpContainer as Subject;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class NoOpContainerTest extends TestCase
{
    /**
     * @return MockObject&Subject
     */
    protected function createSubject()
    {
        $mock = $this->getMockBuilder(Subject::class)
            ->enableProxyingToOriginalMethods()
            ->getMock();

        return $mock;
    }

    public function testMembers()
    {
        {
            $key = uniqid('key');
            $subject = $this->createSubject();
        }

        {
            $subject->set($key, uniqid('value'));
            $this->assertFalse($subject->has($key));

            $this->expectException(NotFoundExceptionInterface::class);
            $subject->get($key);
            $this->expectException(null);

            $this->expectException(ContainerExceptionInterface::class);
            $subject->unset($key);
            $this->expectException(null);

            $subject->clear();

            $clone1 = $subject->withoutKeys([$key]);
            $this->assertNotSame($subject, $clone1);

            $clone2 = $subject->withAddedMappings([$key => uniqid('value2')]);
            $this->assertNotSame($subject, $clone2);
            $this->assertFalse($clone2->has($key));

            $clone3 = $subject->withMappings([$key => uniqid('value3')]);
            $this->assertNotSame($subject, $clone3);
            $this->assertFalse($clone3->has($key));

            $this->assertEquals([], iterator_to_array($subject));
        }
    }
}
