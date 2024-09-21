<?php

namespace Dhii\Container\FuncTest;

use Dhii\Container\DeprefixingContainer;
use Dhii\Container\Exception\NotFoundException;
use Dhii\Container\TestHelpers\ContainerMock;
use Exception;
use PHPUnit\Framework\TestCase;
use function uniqid;

class DeprefixingContainerTest extends TestCase
{
    /**
     * Tests that the subject is able to delegate retrieval to the inner container with a prefixed key.
     *
     * This test uses strict mode for the subject, preventing fallback to the original key.
     *
     * @throws Exception If problem testing.
     */
    public function testGetStrict()
    {
        $prefix = uniqid('prefix');
        $serviceKey = uniqid('service-key');
        $serviceVal = uniqid('service-val');

        $inner = ContainerMock::create($this)->expectHasService($prefix . $serviceKey, $serviceVal);

        $strict = true;
        $subject = new DeprefixingContainer($inner, $prefix, $strict);

        $result = $subject->get($serviceKey);

        $this->assertSame($serviceVal, $result, 'Wrong result retrieved');
    }

    /**
     * Tests that the subject is able to delegate retrieval to the inner container with a prefixed key when strict
     * mode is disabled.
     *
     * @throws Exception If problem testing.
     */
    public function testGetNonStrict()
    {
        $serviceKey = uniqid('service-key');
        $serviceVal = uniqid('service-val');
        $prefix = uniqid('prefix');

        $inner = ContainerMock::create($this);
        $inner->method('get')
              ->withConsecutive([$prefix . $serviceKey], [$serviceKey])
              ->willReturnCallback(function () use ($serviceVal) {
                  static $count = 1;

                  // Throw the first time
                  if ($count === 1) {
                      $count++;
                      throw new NotFoundException();
                  }

                  // Return service the second time
                  return $serviceVal;
              });
        $inner->method('has')
              ->with($serviceKey)
              ->willReturn(true);

        $strict = false;
        $subject = new DeprefixingContainer($inner, $prefix, $strict);

        $result = $subject->get($serviceKey);

        $this->assertSame($serviceVal, $result, 'Wrong result retrieved');
    }

    /**
     * Tests that the subject is able to delegate checking for the prefixed service key to the inner container when
     * strict mode is enabled.
     *
     * @throws Exception If problem testing.
     */
    public function testHasStrict()
    {
        $prefix = uniqid('prefix');
        $serviceKey = uniqid('service-key');
        $serviceVal = uniqid('service-val');

        $inner = ContainerMock::create($this)->expectHasService($prefix . $serviceKey, $serviceVal);

        $strict = true;
        $subject = new DeprefixingContainer($inner, $prefix, $strict);

        $result = $subject->has($serviceKey);

        $this->assertEquals(true, $result, 'Wrong result retrieved');
    }

    /**
     * Tests that the subject is unable to find an un-prefixed entry when strict mode is enabled.
     *
     * @throws Exception If problem testing.
     */
    public function testHasStrictFalse()
    {
        $prefix = uniqid('prefix');
        $serviceKey = uniqid('service-key');

        $inner = ContainerMock::create($this)->expectNotHasService($prefix . $serviceKey);

        $strict = true;
        $subject = new DeprefixingContainer($inner, $prefix, $strict);

        $result = $subject->has($serviceKey);

        $this->assertEquals(false, $result, 'Wrong result retrieved');
    }

    /**
     * Tests that the subject is able to delegate checking for an un-prefixed service key to the inner container when
     * strict mode is disabled.
     *
     * @throws Exception If problem testing.
     */
    public function testHasNonStrict()
    {
        $serviceKey = uniqid('service-key');
        $prefix = uniqid('prefix');

        $inner = ContainerMock::create($this);
        $inner->method('has')
              ->withConsecutive([$prefix . $serviceKey], [$serviceKey])
              ->willReturnCallback(function () {
                  static $count = 1;

                  // Return true on second run
                  return ($count++) === 2;
              });

        $strict = false;
        $subject = new DeprefixingContainer($inner, $prefix, $strict);

        $result = $subject->has($serviceKey);

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

        $inner = ContainerMock::create($this);
        $inner->method('has')->willReturn(false);

        $prefix = uniqid('prefix');
        $strict = false;
        $subject = new DeprefixingContainer($inner, $prefix, $strict);

        $result = $subject->has($serviceKey);

        $this->assertEquals(false, $result, 'Wrong result retrieved');
    }
}
