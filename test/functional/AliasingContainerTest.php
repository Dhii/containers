<?php

namespace Dhii\Container\FuncTest;

use Dhii\Container\AliasingContainer;
use Dhii\Container\Exception\NotFoundException;
use Dhii\Container\TestHelpers\ContainerMock;
use Psr\Container\NotFoundExceptionInterface;
use Exception;
use PHPUnit\Framework\TestCase;
use function uniqid;

class AliasingContainerTest extends TestCase
{
    /**
     * Tests that the subject is able to fetch aliased data from the inner container, using the original key.
     *
     * @since [*next-version*]
     *
     * @throws Exception
     */
    public function testGet()
    {
        $serviceKey = uniqid('key');
        $aliasKey = uniqid('alias');
        $service = uniqid('service');

        $inner = ContainerMock::create($this)->expectHasService($serviceKey, $service);

        $aliases = [
            $aliasKey => $serviceKey,
        ];
        $subject = new AliasingContainer($inner, $aliases);

        $this->assertSame($service, $subject->get($aliasKey), 'Wrong result retrieved');
    }

    /**
     * Tests that the subject is able to fetch non-aliased data from the inner container, using the original key.
     *
     * @since [*next-version*]
     *
     * @throws Exception
     */
    public function testGetNoAlias()
    {
        $serviceKey = uniqid('key');
        $service = uniqid('service');

        $inner = ContainerMock::create($this)->expectHasService($serviceKey, $service);

        $aliases = [];
        $subject = new AliasingContainer($inner, $aliases);

        $result = $subject->get($serviceKey);

        $this->assertSame($service, $result, 'Wrong result retrieved');
    }

    /**
     * Tests that the subject is unable to fetch non-existing data from the inner container.
     *
     * @since [*next-version*]
     *
     * @throws Exception
     */
    public function testGetNotFound()
    {
        $key = uniqid('not-exists');
        $inner = ContainerMock::create($this)->expectNotHasService($key);

        $aliases = [];
        $subject = new AliasingContainer($inner, $aliases);

        try {
            $subject->get($key);

            $this->fail('Subject did not throw an exception');
        } catch (Exception $exception) {
            $this->assertInstanceOf(
                NotFoundExceptionInterface::class,
                $exception,
                'Exception does not implement correct interface'
            );
        }
    }

    /**
     * Tests that the subject is able to look up aliased data in the inner container.
     *
     * @since [*next-version*]
     *
     * @throws Exception
     */
    public function testHas()
    {
        $serviceKey = uniqid('key');
        $aliasKey = uniqid('alias');
        $service = uniqid('service');

        $inner = ContainerMock::create($this)->expectHasService($serviceKey, $service);

        $aliases = [
            $aliasKey => $serviceKey,
        ];
        $subject = new AliasingContainer($inner, $aliases);

        $this->assertTrue($subject->has($aliasKey));
    }

    /**
     * Tests that the subject can look up data in the inner container that was not aliased.
     *
     * @since [*next-version*]
     *
     * @throws Exception
     */
    public function testHasNoAlias()
    {
        $serviceKey = uniqid('key');
        $service = uniqid('service');

        $inner = ContainerMock::create($this)->expectHasService($serviceKey, $service);

        $aliases = [];
        $subject = new AliasingContainer($inner, $aliases);

        $this->assertTrue($subject->has($serviceKey));
    }

    /**
     * Tests that the subject correctly reports that non-existing data in the inner container does not exist.
     *
     * @since [*next-version*]
     *
     * @throws Exception
     */
    public function testHasNotFound()
    {
        $key = uniqid('service-key');
        $inner = ContainerMock::create($this)->expectNotHasService($key);

        $aliases = [];
        $subject = new AliasingContainer($inner, $aliases);

        $this->assertFalse($subject->has($key));
    }
}
