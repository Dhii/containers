<?php

namespace Dhii\Container\FuncTest;

use Dhii\Container\SimpleCacheContainer as Subject;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;
use WildWolf\Psr16MemoryCache;

class SimpleCacheContainerTest extends TestCase
{
    /**
     * Creates a new instance of the test subject.
     *
     * @param CacheInterface $storage
     * @param int            $ttl
     *
     * @return MockObject&Subject The new instance.
     */
    protected function createSubject(CacheInterface $storage, int $ttl)
    {
        $mock = $this->getMockBuilder(Subject::class)
            ->enableProxyingToOriginalMethods()
            ->enableOriginalConstructor()
            ->setConstructorArgs([$storage, $ttl])
            ->getMock();

        return $mock;
    }

    /**
     * @return MockObject&CacheInterface
     */
    protected function createCache(): CacheInterface
    {
        $mock = $this->getMockBuilder(Psr16MemoryCache::class)
            ->enableProxyingToOriginalMethods()
            ->enableOriginalConstructor()
            ->getMock();

        return $mock;
    }

    public function testCrud(): Subject
    {
        {
            $storage = $this->createCache();
            $ttl = rand(1, 999);
            $subject = $this->createSubject($storage, $ttl);
            $key = uniqid('key');
            $val = uniqid('val');
        }

        {
            $this->assertFalse($subject->has($key));
            $subject->set($key, $val);
            $this->assertTrue($subject->has($key));

            $this->assertEquals($val, $subject->get($key));

            $subject->unset($key);
            $this->assertFalse($subject->has($key));
        }

        return $subject;
    }

    public function testClear(): Subject
    {
        {
            $storage = $this->createCache();
            $ttl = rand(1, 999);
            $subject = $this->createSubject($storage, $ttl);
            $data = [
                uniqid('key1') => uniqid('val1'),
                uniqid('key2') => uniqid('val2'),
                uniqid('key3') => uniqid('val3'),
            ];
        }

        {
            foreach ($data as $key => $value) {
                $subject->set($key, $value);
            }

            foreach ($data as $key => $value) {
                $this->assertTrue($subject->has($key));
                $this->assertEquals($data[$key], $subject->get($key));
            }

            $subject->clear();

            foreach ($data as $key => $value) {
                $this->assertFalse($subject->has($key));
            }

            return $subject;
        }
    }
}
