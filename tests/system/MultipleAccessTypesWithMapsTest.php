<?php

namespace Dhii\Container\SysTest;

use Psr\Container\ContainerInterface as PsrContainerInterface;
use Dhii\Container\DataStructureBasedFactory;
use Dhii\Container\DictionaryFactory;
use Dhii\Container\SegmentingContainer;
use PHPUnit\Framework\TestCase;

class MultipleAccessTypesWithMaps__ extends TestCase
{
    const DEV_DB_HOST = 'localhost';
    const STAGING_DB_HOST = '123.staging.myhost';

    public function configDataProvider(): array
    {
        return [
            [ // 1st set
                [ // 1st param
                    'dev' => [
                        'db' => [
                            'host' => self::DEV_DB_HOST,
                            'user' => 'root',
                            'password' => '',
                            'database' => 'my_app',
                        ],
                    ],
                ],
                [ // 2nd param
                    'staging/db/host' => self::STAGING_DB_HOST,
                    'staging/db/user' => 'app123',
                    'staging/db/password' => 'Ad#93rh39d!',
                    'staging/db/database' => 'app123',
                ],
            ],
        ];
    }

    /**
     * Tests whether a structure where some keys are namespaced and some are hierarachical
     * can be normalized into a hierarchy of containers.
     *
     * @dataProvider configDataProvider
     */
    public function testMixedKeyNamespacedToHierarchy(array $devData, $stagingData): PsrContainerInterface
    {
        {
            $data = array_merge($devData, $stagingData);
            $factory = new DataStructureBasedFactory(new DictionaryFactory());
            $container = new SegmentingContainer($factory->createContainerFromArray($data), '/');
        }

        {
            $this->assertEquals(self::STAGING_DB_HOST, $container->get('staging')->get('db')->get('host'));
            $this->assertEquals(self::DEV_DB_HOST, $container->get('dev')->get('db')->get('host'));
        }

        return $container;
    }

    /**
     * @dataProvider configDataProvider
     */
    public function testPreserveIterabilityInHierarchy(array $devData, array $stagingData)
    {
        {
            $factory = new DataStructureBasedFactory(new DictionaryFactory());
            $container = new SegmentingContainer($factory->createContainerFromArray($devData), '/');
        }

        {
            $this->assertIsIterable($container->get('dev')->get('db'));
        }
    }

    /**
     * @dataProvider configDataProvider
     */
    public function testPreserveIterabilityInNamespaced(array $devData, array $stagingData)
    {
        {
            $factory = new DataStructureBasedFactory(new DictionaryFactory());
            $container = new SegmentingContainer($factory->createContainerFromArray($stagingData), '/');
        }

        {
            $this->markTestIncomplete('This will fail, because the segmenting container is not iterable, ' .
                                      'and will return instances of itself for keys that are not found.');
            $this->assertIsIterable($container->get('staging')->get('db'));
        }
    }
}
