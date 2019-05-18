<?php

namespace Dhii\Container\FuncTest;

use Dhii\Container\AliasingContainer as TestSubject;
use Dhii\Container\TestHelpers\ComponentMockeryTrait;
use Dhii\Data\Container\Exception\NotFoundExceptionInterface;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use function uniqid;

class AliasingContainerTest extends TestCase
{
    use ComponentMockeryTrait;

    /**
     * Creates a new instance of the test subject.
     *
     * @param array      $dependencies A list of constructor args.
     * @param array|null $methods      The names of methods to mock in the subject.
     *
     * @return MockObject|TestSubject The new instance.
     * @throws Exception If problem creating.
     */
    protected function createSubject(array $dependencies = [], array $methods = null)
    {
        return $this->createMockBuilder(TestSubject::class, $methods, $dependencies)
                    ->getMock();
    }

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

        $inner = $this->createContainer([
            $serviceKey => $service,
        ]);
        $aliases = [
            $aliasKey => $serviceKey,
        ];
        $subject = $this->createSubject([$inner, $aliases]);

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

        $inner = $this->createContainer([
            $serviceKey => $service,
        ]);
        $aliases = [];
        $subject = $this->createSubject([$inner, $aliases]);

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
        $key = uniqid('service-key');

        $inner = $this->createContainer([
            uniqid('another-key') => uniqid('service'),
        ]);
        $aliases = [];
        $subject = $this->createSubject([$inner, $aliases]);

        try {
            $subject->get($key);

            $this->fail('Subject did not throw an exception');
        } catch (NotFoundExceptionInterface $exception) {
            $this->assertSame($key, $exception->getDataKey());
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

        $inner = $this->createContainer([
            $serviceKey => $service,
        ]);
        $aliases = [
            $aliasKey => $serviceKey,
        ];
        $subject = $this->createSubject([$inner, $aliases]);

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

        $inner = $this->createContainer([
            $serviceKey => $service,
        ]);
        $aliases = [];
        $subject = $this->createSubject([$inner, $aliases]);

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

        $inner = $this->createContainer([
            uniqid('another-key') => uniqid('service'),
        ]);
        $aliases = [];
        $subject = $this->createSubject([$inner, $aliases]);

        $this->assertFalse($subject->has($key));
    }
}
