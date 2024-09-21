<?php

namespace Dhii\Container\FuncTest;

use Dhii\Collection\WritableMapFactoryInterface;
use Dhii\Container\DataStructureBasedFactory as TestSubject;
use Dhii\Container\DictionaryFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DataStructureBasedFactoryTest extends TestCase
{
    /**
     * @return TestSubject&MockObject
     */
    public function createInstance(WritableMapFactoryInterface $mapFactory): TestSubject
    {
        $mock = $this->getMockBuilder(TestSubject::class)
            ->setMethods(null)
            ->setConstructorArgs([$mapFactory])
            ->getMock();

        return $mock;
    }

    /**
     * @return TestSubject
     */
    public function createConfiguredInstance(): TestSubject
    {
        $mapFactory = $this->createWritableMapFactory();
        $subject = $this->createInstance($mapFactory);

        return $subject;
    }

    /**
     * @return DictionaryFactory&MockObject
     */
    public function createWritableMapFactory(): DictionaryFactory
    {
        $mock = $this->getMockBuilder(DictionaryFactory::class)
            ->setMethods(null)
            ->getMock();

        return $mock;
    }

    public function testCreateContainerFromArray()
    {
        {
            $grandchildName = uniqid('grandchild');
            $path = ['children', 0, 'children', 'name'];
            $structure = [
                'name' => 'Bob',
                $path[0] => [
                    $path[1] => [
                        'name' => 'Alex',
                        $path[2] => [
                            $path[3] => $grandchildName,
                            'children' => []
                        ],
                    ]
                ],
            ];
            $subject = $this->createConfiguredInstance();
        }

        {
            $hierarchy = $subject->createContainerFromArray($structure);

            $result = $hierarchy;
            foreach ($path as $key) {
                $result = $result->get($key);
            }

            $this->assertEquals($grandchildName, $result);
        }
    }
}
