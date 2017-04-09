<?php
/*
 * This file is part of the Doctrine Naming Strategy Bundle, an RunOpenCode project.
 *
 * (c) 2017 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\Bundle\DoctrineNamingStrategy\Tests\NamingStrategy;

use Doctrine\ORM\Mapping\UnderscoreNamingStrategy;
use PHPUnit\Framework\TestCase;
use RunOpenCode\Bundle\DoctrineNamingStrategy\NamingStrategy\NamerCollection;
use RunOpenCode\Bundle\DoctrineNamingStrategy\NamingStrategy\UnderscoredBundleNamePrefix;
use RunOpenCode\Bundle\DoctrineNamingStrategy\NamingStrategy\UnderscoredClassNamespacePrefix;
use RunOpenCode\Bundle\DoctrineNamingStrategy\Tests\Fixtures\Bundles\Bar\BarBundle;
use RunOpenCode\Bundle\DoctrineNamingStrategy\Tests\Fixtures\Bundles\Buzz\BuzzBundle;
use RunOpenCode\Bundle\DoctrineNamingStrategy\Tests\Fixtures\Bundles\Foo\FooBundle;

class NamerCollectionTest extends TestCase
{
    /**
     * @test
     */
    public function classToTableName()
    {
        $namer = $this->getNamerCollection();

        $this->assertSame('name', $namer->classToTableName('Some\\Unregistered\\Name'));
        $this->assertSame('first_class_prefix_name', $namer->classToTableName('RunOpenCode\\Bundle\\TestNamespace\\Entity\\Name'));
        $this->assertSame('foo_bundle_prefix_some_entity', $namer->classToTableName('RunOpenCode\\Bundle\\DoctrineNamingStrategy\\Tests\\Fixtures\\Bundles\\Foo\\Entity\\SomeEntity'));
    }
    
    /**
     * @test
     */
    public function firstDifferentWins()
    {
        $namer = new NamerCollection(
            new UnderscoredClassNamespacePrefix(array(
                'map' => array(
                    'RunOpenCode\\Bundle\\TestNamespace\\Entity' => 'my_prefix'
                )
            )),
            array(
                new UnderscoredClassNamespacePrefix(array(
                    'map' => array(
                        'RunOpenCode\\Bundle\\TestNamespace\\Entity' => 'my_other_prefix'
                    )
                )),
                new UnderscoredClassNamespacePrefix(array(
                    'map' => array(
                        'RunOpenCode\\Bundle\\TestNamespace2\\Entity' => 'totaly_different_prefix'
                    )
                ))
            )
        );

        $this->assertSame('my_other_prefix_some_class', $namer->classToTableName('RunOpenCode\\Bundle\\TestNamespace\\Entity\\SomeClass'));
    }

    /**
     * @test
     */
    public function differentStrategiesCanConcatenate()
    {
        $namer = new NamerCollection(
            new UnderscoredClassNamespacePrefix(array(
                'map' => array(
                    'RunOpenCode\\Bundle\\TestNamespace\\Entity' => 'my_prefix'
                )
            )),
            array(
                new UnderscoredClassNamespacePrefix(array(
                    'map' => array(
                        'RunOpenCode\\Bundle\\TestNamespace\\Entity' => 'my_other_prefix'
                    )
                )),
                new UnderscoredClassNamespacePrefix(array(
                    'map' => array(
                        'RunOpenCode\\Bundle\\TestNamespace2\\Entity' => 'totaly_different_prefix'
                    )
                ))
            )
        );

        $this->assertSame('my_other_prefix_some_class_totaly_different_prefix_some_other_class', $namer->joinTableName('RunOpenCode\\Bundle\\TestNamespace\\Entity\\SomeClass', 'RunOpenCode\\Bundle\\TestNamespace2\\Entity\\SomeOtherClass'));
    }

    private function getNamerCollection($concatenation = NamerCollection::UNDERSCORE, $joinTableFieldSuffix = true)
    {
        $namingStrategy = new NamerCollection(new UnderscoreNamingStrategy(), [], [
            'concatenation' => $concatenation,
            'joinTableFieldSuffix' => $joinTableFieldSuffix
        ]);

        $bundle = new UnderscoredBundleNamePrefix($this->mockKernel(), [
            'map' => [
                'FooBundle' => 'foo_bundle_prefix',
                'Bar' => 'bar_bundle_prefix'
            ],
            'joinTableFieldSuffix' => false,
        ]);

        $fqcn = new UnderscoredClassNamespacePrefix([
            'map' => [
                'RunOpenCode\\Bundle\\TestNamespace\\Entity' => 'first_class_prefix',
                'RunOpenCode\\Bundle\\TestNamespace\\Other' => 'second_class_prefix'
            ],
        ]);

        $namingStrategy
            ->registerNamingStrategy($bundle)
            ->registerNamingStrategy($fqcn);

        return $namingStrategy;
    }

    private function mockKernel()
    {
        $stub = $this->getMockBuilder('Symfony\\Component\\HttpKernel\\Kernel')->disableOriginalConstructor()->getMock();

        $stub->method('getBundles')
            ->willReturn([
                new FooBundle(),
                new BarBundle(),
                new BuzzBundle(),
            ]);

        return $stub;
    }
}

