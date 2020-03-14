<?php

namespace Dhii\Container\FuncTest;

use Dhii\Container\Exception\NotFoundException;
use Dhii\Container\MaskingContainer;
use Dhii\Container\TestHelpers\ContainerMock;
use Exception;
use PHPUnit\Framework\TestCase;
use function uniqid;

class MaskingContainerTest extends TestCase
{
    /**
     * Tests that the subject is able to retrieve an exposed entry from the inner container.
     *
     * @throws Exception If problem testing.
     */
    public function testGetExposed()
    {
        $serviceKey = uniqid('service-key');
        $serviceVal = uniqid('service-val');
        $inner = ContainerMock::create($this)->expectHasService($serviceKey, $serviceVal);

        $mask = [
            $serviceKey => true
        ];
        $subject = new MaskingContainer($inner, false, $mask);

        $result = $subject->get($serviceKey);

        $this->assertSame($serviceVal, $result, 'Wrong result retrieved');
    }

    /**
     * Tests that the subject is unable to retrieve a masked entry from the inner container.
     *
     * @throws Exception If problem testing.
     */
    public function testGetMasked()
    {
        $serviceKey = uniqid('service-key');
        $serviceVal = uniqid('service-val');
        $inner = ContainerMock::create($this)->expectHasService($serviceKey, $serviceVal);

        $mask = [
            $serviceKey => false
        ];
        $subject = new MaskingContainer($inner, true, $mask);

        try {
            $subject->get($serviceKey);

            $this->fail('Subject did not throw an exception');
        } catch (NotFoundException $exception) {
            $this->assertTrue(true);
        }
    }

    /**
     * Tests that the subject is able to retrieve an entry, that is implicitly exposed via the default mask, from the
     * inner container.
     *
     * @throws Exception If problem testing.
     */
    public function testGetExposedDefault()
    {
        $serviceKey = uniqid('service-key');
        $serviceVal = uniqid('service-val');
        $inner = ContainerMock::create($this)->expectHasService($serviceKey, $serviceVal);

        $mask = [];
        $subject = new MaskingContainer($inner, true, $mask);

        $result = $subject->get($serviceKey);

        $this->assertSame($serviceVal, $result, 'Wrong result retrieved');
    }

    /**
     * Tests that the subject is unable to retrieve an entry, that is implicitly masked via the default mask, from the
     * inner container.
     *
     * @throws Exception If problem testing.
     */
    public function testGetMaskedDefault()
    {
        $serviceKey = uniqid('service-key');
        $serviceVal = uniqid('service-val');
        $inner = ContainerMock::create($this)->expectHasService($serviceKey, $serviceVal);

        $mask = [
            $serviceKey => false
        ];
        $subject = new MaskingContainer($inner, true, $mask);

        try {
            $subject->get($serviceKey);

            $this->fail('Subject did not throw an exception');
        } catch (NotFoundException $exception) {
            $this->assertTrue(true);
        }
    }

    /**
     * Tests that the subject is able to look up an exposed entry in the inner container.
     *
     * @throws Exception If problem testing.
     */
    public function testHasExposed()
    {
        $serviceKey = uniqid('service-key');
        $serviceVal = uniqid('service-val');
        $inner = ContainerMock::create($this)->expectHasService($serviceKey, $serviceVal);

        $mask = [
            $serviceKey => true
        ];
        $subject = new MaskingContainer($inner, false, $mask);

        $result = $subject->has($serviceKey);

        $this->assertTrue($result);
    }

    /**
     * Tests that the subject is unable to look up a masked entry in the inner container.
     *
     * @throws Exception If problem testing.
     */
    public function testHasMasked()
    {
        $serviceKey = uniqid('service-key');
        $serviceVal = uniqid('service-val');
        $inner = ContainerMock::create($this)->expectHasService($serviceKey, $serviceVal);

        $mask = [
            $serviceKey => false
        ];
        $subject = new MaskingContainer($inner, true, $mask);

        $result = $subject->has($serviceKey);

        $this->assertFalse($result);
    }

    /**
     * Tests that the subject is unable to look up a non-existing exposed entry in the inner container.
     *
     * @throws Exception If problem testing.
     */
    public function testHasExposedFalse()
    {
        $serviceKey = uniqid('service-key');
        $inner = ContainerMock::create($this)->expectNotHasService($serviceKey);

        $mask = [
            $serviceKey => true
        ];
        $subject = new MaskingContainer($inner, false, $mask);

        $result = $subject->has($serviceKey);

        $this->assertFalse($result);
    }

    /**
     * Tests that the subject is unable to look up a non-existing masked entry in the inner container.
     *
     * @throws Exception If problem testing.
     */
    public function testHasMaskedFalse()
    {
        $serviceKey = uniqid('service-key');
        $inner = ContainerMock::create($this)->expectNotHasService($serviceKey);

        $mask = [
            $serviceKey => false
        ];
        $subject = new MaskingContainer($inner, true, $mask);

        $result = $subject->has($serviceKey);

        $this->assertFalse($result);
    }
}
