<?php


namespace Dhii\Wp\Containers\TestHelpers;

use Andrew\Proxy;
use Dhii\Data\Container\Exception\NotFoundExceptionInterface;
use Dhii\Data\Container\WritableContainerInterface;
use Exception;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Container\ContainerInterface;
use Throwable;

trait ComponentMockeryTrait
{
    /**
     * Creates a new instance of the test subject mock.
     *
     * @param string $className The name of the class to mock.
     * @param array|null $methods The methods to mock.
     * Use `null` to not mock anything. Use empty array to mock everything.
     * @param array|null $dependencies The parameters for the subject constructor.
     * Use `null` to disable the original constructor.
     *
     * @return MockBuilder The new builder.
     *
     * @throws Exception If problem creating.
     */
    protected function createMockBuilder(string $className, $methods = [], $dependencies = null)
    {
        $builder = $this->getMockBuilder($className);

        $builder->setMethods($methods);

        if ($dependencies !== null) {
            $builder->enableOriginalConstructor();
            $builder->setConstructorArgs($dependencies);
        } else {
            $builder->disableOriginalConstructor();
        }

        return $builder;
    }

    /**
     * Creates a new abstract class that extends the specified class while implementing the specified interfaces.
     *
     * @param string $className Name of the class to extend.
     * @param array $interfaceNames List of interface names to implement.
     *
     * @return string The name of the new class.
     *
     * @throws Exception If problem creating.
     * @throws Throwable If problem running.
     */
    protected function createImplementingClass(
        string $className,
        array $interfaceNames = []
    ) {
        $newClassName = uniqid(sprintf('%1$s_', $className));
        $implements = count($interfaceNames)
            ? 'implements ' . implode(', ', $interfaceNames)
            : '';
        $class = <<<PHP
    abstract class $newClassName extends $className $implements {}
PHP;
        eval($class);

        return $newClassName;
    }

    /**
     * Creates a new Dhii Container - Not Found exception.
     *
     * @param string $message Error message.
     * @param Throwable|null $previous Inner exception.
     * @param ContainerInterface|null $container The container, if any.
     * @param string|null $dataKey The data key, if any.
     *
     * @return MockObject|NotFoundExceptionInterface The new exception.
     *
     * @throws Exception If problem creating.
     * @throws Throwable If problem running.
     */
    protected function createNotFoundException(
        string $message,
        Throwable $previous = null,
        ContainerInterface $container = null,
        string $dataKey = null
    ) {
        $eClass = $this->createImplementingClass('Exception', [NotFoundExceptionInterface::class]);
        $e = $this->createMockBuilder(
            $eClass,
            ['getContainer', 'getDataKey'],
            [
                $message,
                0,
                $previous,
            ]
        )->getMock();
        $e->method('getContainer')
            ->willReturn($container);
        $e->method('getDataKey')
            ->willReturn($dataKey);

        return $e;
    }

    /**
     * @return callable|MockObject
     *
     * @throws Exception If problem creating.
     */
    protected function createCallable(callable $callable): callable
    {
        static $className = null;

        if (!$className) {
            $className = uniqid('MockInvocable');
        }

        if (!interface_exists($className)) {
            $class = <<<EOL
interface $className
{
    public function __invoke();
}
EOL;
            eval($class);
        }

        $mock = $this->getMockBuilder($className)
            ->setMethods(['__invoke'])
            ->getMock();

        $mock->method('__invoke')
            ->willReturnCallback($callable);

        assert(is_callable($mock));

        return $mock;
    }

    /**
     * Creates a new mock container.
     *
     * @param array $services The map of service name to service value.
     *
     * @return ContainerInterface|MockObject
     *
     * @throws Exception If problem creating.
     */
    protected function createContainer(array $services = [])
    {
        $mock = $this->getMockBuilder(ContainerInterface::class)
            ->setMethods(['has', 'get'])
            ->getMock();
        assert($mock instanceof ContainerInterface);

        $mock->method('get')
            ->willReturnCallback(function ($key) use ($services, $mock) {
                if (!isset($services[$key])) {
                    throw $this->createNotFoundException(
                        sprintf('No entry found for key "%1$s"', $key),
                        null,
                        $mock,
                        $key
                    );

                    throw $e;
                }

                return $services[$key];
            });

        return $mock;
    }

    /**
     * Creates a new mock writable container.
     *
     * @param array $services The map of service name to service value.
     *
     * @return WritableContainerInterface|MockObject
     *
     * @throws Exception If problem creating.
     */
    protected function createWritableContainer(array $services = []): WritableContainerInterface
    {
        $mock = $this->getMockBuilder(WritableContainerInterface::class)
            ->setMethods(['has', 'get', 'set', 'delete'])
            ->getMock();
        assert($mock instanceof WritableContainerInterface);

        $mock->method('get')
            ->willReturnCallback(function ($key) use ($services, $mock) {
                if (!isset($services[$key])) {
                    throw $this->createNotFoundException(
                        sprintf('No entry found for key "%1$s"', $key),
                        null,
                        $mock,
                        $key
                    );
                }

                return $services[$key];
            });

        return $mock;
    }

    /**
     * Creates an array.
     *
     * @param int $length The length of the array.
     * @param callable $valueGenerator This generates the values. Called for each index.
     * @param callable|null $keyGenerator This generates the keys. Called for each index.
     * Default: a generator that returns the index. Useful for numeric arrays.
     *
     * @return array The array of specified length, with generated keys and values.
     */
    public function createArray(int $length, callable $valueGenerator, callable $keyGenerator = null)
    {
        $result = [];
        $keyGenerator = $keyGenerator ?? function (int $index) {
            return $index;
        };

        for ($i=0; $i<$length-1; $i++) {
            $key = $keyGenerator($i);
            $value = $valueGenerator($i);
            $result[$key] = $value;
        }

        return $result;
    }

    /**
     * Creates a proxy that allows public access to the object's protected members.
     *
     * @param object $object The object to proxy.
     *
     * @return Proxy the new proxy.
     */
    protected function proxy($object): Proxy
    {
        return new Proxy($object);
    }

    /**
     * Requiring PHPUnit TestCase's method.
     *
     * @param string $className Name of the class to get a builder for.
     *
     * @return MockBuilder The mock builder.
     */
    abstract function getMockBuilder($className);
}