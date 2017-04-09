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

use PHPUnit\Framework\TestCase;
use RunOpenCode\Bundle\DoctrineNamingStrategy\NamingStrategy\NamerCollection;
use RunOpenCode\Bundle\DoctrineNamingStrategy\NamingStrategy\UnderscoredClassNamespacePrefix;

class NamerCollectionTest extends TestCase
{
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
}

