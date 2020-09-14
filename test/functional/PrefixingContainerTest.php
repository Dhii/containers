<?php

namespace Dhii\Container\FuncTest;

use Dhii\Container\Exception\NotFoundException;
use Dhii\Container\PrefixingContainer;
use Dhii\Container\TestHelpers\ContainerMock;
use PHPUnit\Framework\TestCase;
use function uniqid;

/**
 * Tests the prefixing container implementation.
 *
 * Tests with "Strict" in the method name test strict prefixing containers, while tests "Permissive" in the name test
 * prefixing containers in non-strict mode.
 *
 * Tests with "NotExists" in the method name test scenarios where the key does not exist in the inner container.
 *
 * Tests with "NoPrefix" in the method name test scenarios where the inner container has the key but the callee does
 * not prefix the key that is passed to the outer container.
 *
 * @since [*next-version*]
 */
class PrefixingContainerTest extends TestCase
{
    /**
     * @since [*next-version*]
     */
    public function testGetStrict()
    {
        {
            $prefix = uniqid('prefix');
            $innerKey = uniqid('key');
            $outerKey = $prefix . $innerKey;
        }
        {
            $value = uniqid('value');
            $inner = ContainerMock::create($this)->expectGet([
                $innerKey => $value
            ]);
        }
        {
            $strict = true;
            $subject = new PrefixingContainer($inner, $prefix, $strict);
        }

        // Fetching the prefixed key should return the value of the un-prefixed key from the inner container
        $result = $subject->get($outerKey);

        $this->assertSame($value, $result);
    }

    /**
     * @since [*next-version*]
     */
    public function testGetPermissive()
    {
        {
            $prefix = uniqid('prefix');
            $innerKey = uniqid('key');
            $outerKey = $prefix . $innerKey;
        }
        {
            $value = uniqid('value');
            $inner = ContainerMock::create($this)->expectGet([
                $innerKey => $value
            ]);
        }
        {
            $strict = false;
            $subject = new PrefixingContainer($inner, $prefix, $strict);
        }

        // Fetching the prefixed key should return the value of the un-prefixed key from the inner container
        $result = $subject->get($outerKey);

        $this->assertSame($value, $result);
    }

    /**
     * @since [*next-version*]
     */
    public function testGetNotExistsStrict()
    {
        {
            $prefix = uniqid('prefix');
            $innerKey = uniqid('key');
            $outerKey = $prefix . $innerKey;
        }
        {
            $inner = ContainerMock::create($this)->expectGet([
                $innerKey => new NotFoundException()
            ]);
        }
        {
            $strict = true;
            $subject = new PrefixingContainer($inner, $prefix, $strict);
        }

        // Fetching the prefixed key should throw an exception
        $this->expectException(NotFoundException::class);

        $subject->get($outerKey);
    }

    /**
     * @since [*next-version*]
     */
    public function testGetNotExistsPermissive()
    {
        {
            $prefix = uniqid('prefix');
            $innerKey = uniqid('key');
            $outerKey = $prefix . $innerKey;
        }
        {
            $inner = ContainerMock::create($this)->expectGet([
                $innerKey => new NotFoundException(),
                $outerKey => new NotFoundException(),
            ]);
        }
        {
            $strict = false;
            $subject = new PrefixingContainer($inner, $prefix, $strict);
        }

        // Fetching the prefixed key should throw an exception
        $this->expectException(NotFoundException::class);

        $subject->get($outerKey);
    }

    /**
     * @since [*next-version*]
     */
    public function testGetNoPrefixStrict()
    {
        {
            $prefix = uniqid('prefix');
            $innerKey = uniqid('key');
        }
        {
            $inner = ContainerMock::create($this);
        }
        {
            $strict = true;
            $subject = new PrefixingContainer($inner, $prefix, $strict);
        }

        // Fetching the un-prefixed key should throw an exception without querying the inner container
        $this->expectException(NotFoundException::class);

        $subject->get($innerKey);
    }

    /**
     * @since [*next-version*]
     */
    public function testGetNoPrefixPermissive()
    {
        {
            $prefix = uniqid('prefix');
            $innerKey = uniqid('key');
        }
        {
            $value = uniqid('value');
            $inner = ContainerMock::create($this)->expectGet([
                $innerKey => $value,
            ]);
        }
        {
            // The outer container is a non-strict prefixing container
            $strict = false;
            $subject = new PrefixingContainer($inner, $prefix, $strict);
        }

        // Fetching the un-prefixed key should return the inner container's value for that key
        $result = $subject->get($innerKey);

        $this->assertSame($value, $result);
    }

    /**
     * @since [*next-version*]
     */
    public function testHasTrueStrict()
    {
        {
            $prefix = uniqid('prefix');
            $innerKey = uniqid('key');
            $outerKey = $prefix . $innerKey;
        }
        {
            $value = uniqid('value');
            $inner = ContainerMock::create($this)->expectGet([
                $innerKey => $value,
            ]);
        }
        {
            $strict = true;
            $subject = new PrefixingContainer($inner, $prefix, $strict);
        }

        $result = $subject->has($outerKey);

        $this->assertTrue($result);
    }

    /**
     * @since [*next-version*]
     */
    public function testHasTruePermissive()
    {
        {
            $prefix = uniqid('prefix');
            $innerKey = uniqid('key');
            $outerKey = $prefix . $innerKey;
        }
        {
            $value = uniqid('value');
            $inner = ContainerMock::create($this)->expectGet([
                $innerKey => $value,
            ]);
        }
        {
            $strict = false;
            $subject = new PrefixingContainer($inner, $prefix, $strict);
        }

        $result = $subject->has($outerKey);

        $this->assertTrue($result);
    }

    /**
     * @since [*next-version*]
     */
    public function testHasFalseStrict()
    {
        {
            $prefix = uniqid('prefix');
            $innerKey = uniqid('key');
            $outerKey = $prefix . $innerKey;
        }
        {
            $inner = ContainerMock::create($this)->expectGet([
                $innerKey => new NotFoundException(),
            ]);
        }
        {
            $strict = true;
            $subject = new PrefixingContainer($inner, $prefix, $strict);
        }

        $result = $subject->has($outerKey);

        $this->assertFalse($result);
    }

    /**
     * @since [*next-version*]
     */
    public function testHasFalsePermissive()
    {
        {
            $prefix = uniqid('prefix');
            $innerKey = uniqid('key');
            $outerKey = $prefix . $innerKey;
        }
        {
            $inner = ContainerMock::create($this)->expectGet([
                $innerKey => new NotFoundException(),
                $outerKey => new NotFoundException(),
            ]);
        }
        {
            $strict = false;
            $subject = new PrefixingContainer($inner, $prefix, $strict);
        }

        $result = $subject->has($outerKey);

        $this->assertFalse($result);
    }

    /**
     * @since [*next-version*]
     */
    public function testHasNoPrefixStrict()
    {
        {
            $prefix = uniqid('prefix');
            $innerKey = uniqid('key');
        }
        {
            $value = uniqid('value');
            $inner = ContainerMock::create($this)->expectGet([
                $innerKey => $value,
            ]);
        }
        {
            $strict = true;
            $subject = new PrefixingContainer($inner, $prefix, $strict);
        }

        $result = $subject->has($innerKey);

        $this->assertFalse($result);
    }

    /**
     * @since [*next-version*]
     */
    public function testHasNoPrefixPermissive()
    {
        {
            $prefix = uniqid('prefix');
            $innerKey = uniqid('key');
        }
        {
            $value = uniqid('value');
            $inner = ContainerMock::create($this)->expectGet([
                $innerKey => $value,
            ]);
        }
        {
            $strict = false;
            $subject = new PrefixingContainer($inner, $prefix, $strict);
        }

        $result = $subject->has($innerKey);

        $this->assertTrue($result);
    }
}
