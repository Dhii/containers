<?php

namespace Dhii\Container\FuncTest;

use Dhii\Container\ServiceProvider;
use Exception;
use PHPUnit\Framework\TestCase;

class ServiceProviderTest extends TestCase
{
    /**
     * Tests that the extensions passed are correctly retrieved.
     *
     * @throws Exception If problem testing.
     */
    public function testGetExtensions()
    {
        $extensions = [
            'one' => function () {},
            'two' => function () {},
        ];
        $subject = new ServiceProvider([], $extensions);

        $this->assertEquals(
            $extensions,
            $subject->getExtensions(),
            'Wrong extensions retrieved',
            0.0,
            10,
            true
        );
    }

    /**
     * Tests that the factories passed are correctly retrieved.
     *
     * @throws Exception If problem testing.
     */
    public function testGetFactories()
    {
        $factories = [
            'three' => function () {},
            'four'  => function () {},
        ];
        $subject = new ServiceProvider($factories, []);

        $this->assertEquals(
            $factories,
            $subject->getFactories(),
            'Wrong factories retrieved',
            0.0,
            10,
            true
        );
    }
}
