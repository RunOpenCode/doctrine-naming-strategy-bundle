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
use RunOpenCode\Bundle\DoctrineNamingStrategy\NamingStrategy\UnderscoredClassNamespacePrefix;

class UnderscoredClassNamespacePrefixTest extends TestCase
{
    /**
     * @test
     *
     * @expectedException \RunOpenCode\Bundle\DoctrineNamingStrategy\Exception\RuntimeException
     */
    public function invalidConfiguration()
    {
        new UnderscoredClassNamespacePrefix([
            'blacklist' => [
                'RunOpenCode\\Bundle\\TestNamespace\\Entity' => 'my_prefix',
            ],
            'whitelist' => [
                'RunOpenCode\\Bundle\\TestNamespace\\Entity' => 'my_prefix',
            ],
        ]);
    }

    /**
     * @test
     */
    public function classToTableNameLowercase()
    {
        $strategy = new UnderscoredClassNamespacePrefix([
            'map' => [
                'RunOpenCode\\Bundle\\TestNamespace\\Entity' => 'my_prefix',
            ],
        ]);

        $this->assertSame('my_prefix_some_entity', $strategy->classToTableName('RunOpenCode\\Bundle\\TestNamespace\\Entity\\SomeEntity'));
    }

    /**
     * @test
     */
    public function classToTableNameUppercase()
    {
        $strategy = new UnderscoredClassNamespacePrefix([
            'map' => [
                'RunOpenCode\\Bundle\\TestNamespace\\Entity' => 'my_prefix',
            ],
            'case' => CASE_UPPER,
        ]);

        $this->assertSame('MY_PREFIX_SOME_ENTITY', $strategy->classToTableName('RunOpenCode\\Bundle\\TestNamespace\\Entity\\SomeEntity'));
    }

    /**
     * @test
     */
    public function propertyToColumnNameLowercase()
    {
        $strategy = new UnderscoredClassNamespacePrefix();

        $this->assertSame('some_object_property', $strategy->propertyToColumnName('someObjectProperty'));
    }

    /**
     * @test
     */
    public function propertyToColumnNameUppercase()
    {
        $strategy = new UnderscoredClassNamespacePrefix([
            'case' => CASE_UPPER,
        ]);

        $this->assertSame('SOME_OBJECT_PROPERTY', $strategy->propertyToColumnName('someObjectProperty'));
    }

    /**
     * @test
     */
    public function embeddedFieldToColumnNameLowercase()
    {
        $strategy = new UnderscoredClassNamespacePrefix();

        $this->assertSame('some_object_property_embedded_column_name', $strategy->embeddedFieldToColumnName('someObjectProperty', 'embeddedColumnName'));
    }

    /**
     * @test
     */
    public function embeddedFieldToColumnNameUppercase()
    {
        $strategy = new UnderscoredClassNamespacePrefix([
            'case' => CASE_UPPER,
        ]);

        $this->assertSame('SOME_OBJECT_PROPERTY_EMBEDDED_COLUMN_NAME', $strategy->embeddedFieldToColumnName('someObjectProperty', 'embeddedColumnName'));
    }

    /**
     * @test
     */
    public function referenceColumnName()
    {
        $strategy = new UnderscoredClassNamespacePrefix();
        $this->assertSame('id', $strategy->referenceColumnName());

        $strategy = new UnderscoredClassNamespacePrefix([
            'case' => CASE_UPPER,
        ]);
        $this->assertSame('ID', $strategy->referenceColumnName());
    }


    /**
     * @test
     */
    public function joinColumnNameLowercase()
    {
        $strategy = new UnderscoredClassNamespacePrefix();

        $this->assertSame('a_property_id', $strategy->joinColumnName('aProperty'));
    }

    /**
     * @test
     */
    public function joinColumnNameUppercase()
    {
        $strategy = new UnderscoredClassNamespacePrefix([
            'case' => CASE_UPPER,
        ]);

        $this->assertSame('A_PROPERTY_ID', $strategy->joinColumnName('aProperty'));
    }

    /**
     * @test
     */
    public function joinTableNameLowercase()
    {
        $strategy = new UnderscoredClassNamespacePrefix([
            'map' => [
                'RunOpenCode\\Bundle\\TestNamespace\\Entity' => 'my_prefix',
                'RunOpenCode\\Bundle\\TestNamespace\\Other' => 'my_other_prefix'
            ],
        ]);

        $this->assertSame('my_prefix_some_entity_my_other_prefix_some_entity', $strategy->joinTableName(
            'RunOpenCode\\Bundle\\TestNamespace\\Entity\\SomeEntity',
            'RunOpenCode\\Bundle\\TestNamespace\\Other\\SomeEntity'
        ));

        $this->assertSame('my_prefix_some_entity_my_other_prefix_some_entity_field_name', $strategy->joinTableName(
            'RunOpenCode\\Bundle\\TestNamespace\\Entity\\SomeEntity',
            'RunOpenCode\\Bundle\\TestNamespace\\Other\\SomeEntity',
            'fieldName'
        ));
    }

    /**
     * @test
     */
    public function joinTableNameUppercase()
    {
        $strategy = new UnderscoredClassNamespacePrefix([
            'map' => [
                'RunOpenCode\\Bundle\\TestNamespace\\Entity' => 'my_prefix',
                'RunOpenCode\\Bundle\\TestNamespace\\Other' => 'my_other_prefix'
            ],
            'case' => CASE_UPPER,
        ]);

        $this->assertSame('MY_PREFIX_SOME_ENTITY_MY_OTHER_PREFIX_SOME_ENTITY', $strategy->joinTableName(
            'RunOpenCode\\Bundle\\TestNamespace\\Entity\\SomeEntity',
            'RunOpenCode\\Bundle\\TestNamespace\\Other\\SomeEntity'
        ));


        $this->assertSame('MY_PREFIX_SOME_ENTITY_MY_OTHER_PREFIX_SOME_ENTITY_FIELD_NAME', $strategy->joinTableName(
            'RunOpenCode\\Bundle\\TestNamespace\\Entity\\SomeEntity',
            'RunOpenCode\\Bundle\\TestNamespace\\Other\\SomeEntity',
            'fieldName'
        ));
    }

    /**
     * @test
     */
    public function joinTableNameDisableJoinTableFieldSuffix()
    {
        $strategy = new UnderscoredClassNamespacePrefix([
            'map' => [
                'RunOpenCode\\Bundle\\TestNamespace\\Entity' => 'my_prefix',
                'RunOpenCode\\Bundle\\TestNamespace\\Other' => 'my_other_prefix'
            ],
            'joinTableFieldSuffix' => false,
        ]);

        $this->assertSame('my_prefix_some_entity_my_other_prefix_some_entity', $strategy->joinTableName(
            'RunOpenCode\\Bundle\\TestNamespace\\Entity\\SomeEntity',
            'RunOpenCode\\Bundle\\TestNamespace\\Other\\SomeEntity',
            'fieldName'
        ));
    }

    /**
     * @test
     */
    public function joinKeyColumnNameLowercase()
    {
        $strategy = new UnderscoredClassNamespacePrefix([
            'map' => [
                'RunOpenCode\\Bundle\\TestNamespace\\Entity' => 'my_prefix',
                'RunOpenCode\\Bundle\\TestNamespace\\Other' => 'my_other_prefix'
            ],
        ]);

        $this->assertSame('my_prefix_some_entity_id', $strategy->joinKeyColumnName(
            'RunOpenCode\\Bundle\\TestNamespace\\Entity\\SomeEntity'
        ));

        $this->assertSame('my_other_prefix_some_entity_fk_id', $strategy->joinKeyColumnName(
            'RunOpenCode\\Bundle\\TestNamespace\\Other\\SomeEntity',
            'fkId'
        ));
    }

    /**
     * @test
     */
    public function joinKeyColumnNameUppercase()
    {
        $strategy = new UnderscoredClassNamespacePrefix([
            'map' => [
                'RunOpenCode\\Bundle\\TestNamespace\\Entity' => 'my_prefix',
                'RunOpenCode\\Bundle\\TestNamespace\\Other' => 'my_other_prefix'
            ],
            'case' => CASE_UPPER,
        ]);

        $this->assertSame('MY_PREFIX_SOME_ENTITY_ID', $strategy->joinKeyColumnName(
            'RunOpenCode\\Bundle\\TestNamespace\\Entity\\SomeEntity'
        ));

        $this->assertSame('MY_OTHER_PREFIX_SOME_ENTITY_FK_ID', $strategy->joinKeyColumnName(
            'RunOpenCode\\Bundle\\TestNamespace\\Other\\SomeEntity',
            'fkId'
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
                'RunOpenCode\\Bundle\\TestNamespace\\Entity' => 'my_prefix',
                'RunOpenCode\\Bundle2\\Entity' => 'whitelisted_prefix'
            ),
            'whitelist' => array(
                'RunOpenCode\\Bundle2'
            )
        ));

        $this->assertSame('some_entity', $strategy->classToTableName('RunOpenCode\\Bundle\\DoctrineNamingStrategy\\Entity\\SomeEntity'));
        $this->assertSame('whitelisted_prefix_some_entity', $strategy->classToTableName('RunOpenCode\\Bundle2\\Entity\\SomeEntity'));
    }
}
