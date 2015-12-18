<?php
/*
 * This file is part of the Doctrine Naming Strategy Bundle, an RunOpenCode project.
 *
 * (c) 2015 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\Bundle\DoctrineNamingStrategy\Tests\NamingStrategy;

use RunOpenCode\Bundle\DoctrineNamingStrategy\NamingStrategy\UnderscoredClassNamespacePrefix;

class UnderscoredClassNamespacePrefixTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function classToTableNameLowercase()
    {
        $strategy = new UnderscoredClassNamespacePrefix(array(
            'map' => array(
                'RunOpenCode\\Bundle\\TestNamespace\\Entity' => 'my_prefix'
            )
        ));

        $this->assertSame('my_prefix_some_entity', $strategy->classToTableName('RunOpenCode\\Bundle\\TestNamespace\\Entity\\SomeEntity'));
    }

    /**
     * @test
     */
    public function classToTableNameUppercase()
    {
        $strategy = new UnderscoredClassNamespacePrefix(array(
            'map' => array(
                'RunOpenCode\\Bundle\\TestNamespace\\Entity' => 'my_prefix'
            ),
            'case' => CASE_UPPER
        ));

        $this->assertSame('MY_PREFIX_SOME_ENTITY', $strategy->classToTableName('RunOpenCode\\Bundle\\TestNamespace\\Entity\\SomeEntity'));
    }

    /**
     * @test
     */
    public function joinTableName()
    {
        $strategy = new UnderscoredClassNamespacePrefix(array(
            'map' => array(
                'RunOpenCode\\Bundle\\TestNamespace\\Entity' => 'my_prefix',
                'RunOpenCode\\Bundle\\TestNamespace\\Other' => 'my_other_prefix'
            )
        ));

        $this->assertSame('my_prefix_some_entity_my_other_prefix_some_entity_field_name', $strategy->joinTableName(
            'RunOpenCode\\Bundle\\TestNamespace\\Entity\\SomeEntity',
            'RunOpenCode\\Bundle\\TestNamespace\\Other\\SomeEntity',
            'fieldName'
        ));
    }

    /**
     * @test
     *
     * @expectedException \RuntimeException
     */
    public function invalidConfiguration()
    {
        new UnderscoredClassNamespacePrefix(array(
            'map' => array(
                'RunOpenCode\\Bundle\\TestNamespace\\Entity' => 'my_prefix'
            ),
            'blacklist' => array(
                'Test\\Bundle'
            ),
            'whitelist' => array(
                'Test\\Bundle2'
            )
        ));
    }

    /**
     * @test
     */
    public function blacklisted()
    {
        $strategy = new UnderscoredClassNamespacePrefix(array(
            'map' => array(
                'RunOpenCode\\Bundle\\TestNamespace\\Entity' => 'my_prefix'
            ),
            'blacklist' => array(
                'RunOpenCode\\Bundle'
            )
        ));

        $this->assertSame('some_entity', $strategy->classToTableName('RunOpenCode\\Bundle\\DoctrineNamingStrategy\\Entity\\SomeEntity'));
    }

    /**
     * @test
     */
    public function whitelisted()
    {
        $strategy = new UnderscoredClassNamespacePrefix(array(
            'map' => array(
                'RunOpenCode\\Bundle\\TestNamespace\\Entity' => 'my_prefix'
            ),
            'whitelist' => array(
                'Test\\Bundle2'
            )
        ));

        $this->assertSame('some_entity', $strategy->classToTableName('RunOpenCode\\Bundle\\DoctrineNamingStrategy\\Entity\\SomeEntity'));
    }
}
