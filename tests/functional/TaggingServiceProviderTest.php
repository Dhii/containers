<?php

namespace Dhii\Container\FuncTest;

use Dhii\Collection\ContainerInterface;
use Dhii\Container\DelegatingContainer;
use Dhii\Container\ServiceProvider;
use Dhii\Container\TaggingServiceProvider;
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
}
