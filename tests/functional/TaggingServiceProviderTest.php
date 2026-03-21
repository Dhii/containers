<?php

namespace Dhii\Container\FuncTest;

use Dhii\Collection\ContainerInterface;
use Dhii\Container\DelegatingContainer;
use Dhii\Container\ServiceProvider;
use Dhii\Container\TaggingServiceProvider;
use Dhii\Container\TestHelpers\MethodClass;
use Exception;
use PHPUnit\Framework\TestCase;

class TaggingServiceProviderTest extends TestCase
{
    /**
     * Tests that the extensions passed are correctly retrieved.
     *
     * @throws Exception If problem testing.
     */
    public function testTagsRecognized()
    {
        $factories = [
            'serviceX' =>
                fn (): string => 'X',
            'serviceY' =>
                /**
                 * This @tag my_tag is misplaced.
                 */
                fn (): string => 'Y',
            'serviceZ' =>
                /** This @tag my_tag is misplaced. */
                fn (): string => 'Z',
            'serviceA' =>
                /**
                 * @tag my_tag
                 */
                fn (): string => 'A',
            'serviceB' =>
                /** @tag my_tag */
                function (): string {
                    return 'B';
                },
            'serviceC' =>
                /**
                 * @tag my_tag
                 */
                new class () {
                    public function __invoke(): string
                    {
                        return 'C';
                    }
                },
            'serviceD' => fn (ContainerInterface $c): string =>
                implode('', array_merge($c->get('my_tag'), ['D'])),
        ];
        $extensions = [];
        $inner = new ServiceProvider($factories, $extensions);
        $subject = new TaggingServiceProvider($inner);
        $container = new DelegatingContainer($subject, null);

        $result = $container->get('serviceD');
        $this->assertEquals('ABCD', $result);
    }

    /**
     * Tests that tag detection is silently skilled for array and string callables, which cannot have reflections.
     *
     * @throws Exception If problem testing.
     */
    public function testArrayAndStringCallables()
    {
        $instance = new MethodClass();

        $factories = [
            'serviceW' =>
                /**
                 * This docblock isn't applied to anything
                 *
                 * @tag my_tag
                 */
                'uniqid',
            'serviceX' =>
                /**
                 * This docblock isn't applied to anything
                 *
                 * @tag my_tag
                 */
                [$instance, 'instanceMethod'],
            'serviceY' =>
                /**
                 * This docblock isn't applied to anything
                 *
                 * @tag my_tag
                 */
                [MethodClass::class, 'staticMethod'],
            'serviceZ' =>
                /**
                 * This docblock isn't applied to anything
                 *
                 * @tag my_tag
                 */
                sprintf('%1$s::%2$s', MethodClass::class, 'staticMethod'),
            'serviceA' =>
                /**
                 * @tag my_tag
                 */
                fn (): string => 'A',
            'serviceB' =>
                /** @tag my_tag */
                function (): string {
                    return 'B';
                },
            'serviceC' =>
                /**
                 * @tag my_tag
                 */
                new class () {
                    public function __invoke(): string
                    {
                        return 'C';
                    }
                },
            'serviceD' => fn (ContainerInterface $c): string =>
                implode('', array_merge($c->get('my_tag'), ['D'])),
        ];
        $extensions = [];
        $inner = new ServiceProvider($factories, $extensions);
        $subject = new TaggingServiceProvider($inner);
        $container = new DelegatingContainer($subject, null);

        $result = $container->get('serviceD');
        $this->assertEquals('ABCD', $result);
    }
}
