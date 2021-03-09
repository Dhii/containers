<?php

namespace Dhii\Container\FuncTest;

use Dhii\Collection\MutableContainerInterface;
use Dhii\Container\FlashContainer as Subject;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FlashContainerTest extends TestCase
{
    /**
     * Creates a new instance of the test subject.
     *
     * @param MutableContainerInterface $storage
     * @param string                    $key
     *
     * @return MockObject&Subject The new instance.
     */
    protected function createSubject(MutableContainerInterface $storage, string $key)
    {
        $mock = $this->getMockBuilder(Subject::class)
            ->enableProxyingToOriginalMethods()
            ->enableOriginalConstructor()
            ->setConstructorArgs([$storage, $key])
            ->getMock();

        return $mock;
    }

    /**
     * @return MockObject&MutableContainerInterface
     */
    protected function createStorage(array $data): MutableContainerInterface
    {
        $mock = $this->getMockBuilder(MutableContainerInterface::class)
            ->setMethods(['has', 'get', 'set', 'unset'])
            ->getMockForAbstractClass();

        $mock->data = $data;

        $mock->method('has')
            ->with($this->isType('string'))
            ->will($this->returnCallback(function (string $key) use ($mock) {
                return array_key_exists($key, $mock->data);
            }));
        $mock->method('get')
            ->with($this->isType('string'))
            ->will($this->returnCallback(function (string $key) use ($mock) {
                return array_key_exists($key, $mock->data)
                    ? $mock->data[$key]
                    : null;
            }));
        $mock->method('set')
            ->with($this->isType('string'), $this->anything())
            ->will($this->returnCallback(function (string $key, $value) use ($mock) {
                $mock->data[$key] = $value;
            }));
        $mock->method('unset')
            ->with($this->isType('string'))
            ->will($this->returnCallback(function (string $key) use ($mock) {
                if (array_key_exists($key, $mock->data)) {
                    unset($mock->data[$key]);
                }
            }));

        return $mock;
    }

    /**
     * Tests that flash data operations work correctly, and only affect the designated key of the storage.
     *
     * @return Subject
     */
    public function testCrud(): Subject
    {
        {
            $key1 = uniqid('key1');
            $val1 = uniqid('val1');
            $key2 = uniqid('key2');
            $val2 = uniqid('val2');
            $initialFlashData = [$key1 => $val1];
            $otherStorageKey = uniqid('other-storage-key');
            $otherStorageValue = uniqid('other-storage-value');
            $storageKey = uniqid('storage-key');
            $storage = $this->createStorage([
                $storageKey => $initialFlashData,
                $otherStorageKey => $otherStorageValue,
            ]);
            $subject = $this->createSubject($storage, $storageKey);
        }

        {
            // Initialization
            $this->assertEquals($initialFlashData, $storage->get($storageKey), 'Initial flash data not set in storage');
            $this->assertFalse($subject->has($key1), 'Flash data available before initialization');
            $subject->init();
            $this->assertEquals([], $storage->get($storageKey), 'Flash data not cleared from storage');

            // Retrieval
            $this->assertEquals($val1, $subject->get($key1));

            // Writing
            $subject->set($key2, $val2);
            $this->assertEquals($val2, $subject->get($key2), 'Flash value not set correctly');
            $this->assertEquals(array_merge($initialFlashData, [$key2 => $val2]), $storage->get($storageKey), 'Flash value not persisted correctly');

            // Deleting
            $subject->unset($key1);
            $this->assertFalse($subject->has($key1), 'Flash value not unset correctly');
            $this->assertEquals([$key2 => $val2], $storage->get($storageKey), 'Flash value removal not persisted correctly');

            // Clearing
            $subject->clear();
            $this->assertFalse($subject->has($key2), 'Flash data was not cleared correctly');
            $this->assertEquals([], $storage->get($storageKey), 'Flash data clearing not persisted correctly');

            // Isolation
            $this->assertEquals($otherStorageValue, $storage->get($otherStorageKey), 'Other storage values affected by flash data');
        }

        return $subject;
    }
}
