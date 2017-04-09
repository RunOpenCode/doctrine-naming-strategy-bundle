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
use RunOpenCode\Bundle\DoctrineNamingStrategy\NamingStrategy\UnderscoredBundleNamePrefix;
use RunOpenCode\Bundle\DoctrineNamingStrategy\Tests\Fixtures\Bundles\Bar\BarBundle;
use RunOpenCode\Bundle\DoctrineNamingStrategy\Tests\Fixtures\Bundles\Buzz\BuzzBundle;
use RunOpenCode\Bundle\DoctrineNamingStrategy\Tests\Fixtures\Bundles\Foo\FooBundle;

class UnderscoredBundleNamePrefixTest extends TestCase
{
     /**
     * @test
     *
     * @expectedException \RunOpenCode\Bundle\DoctrineNamingStrategy\Exception\RuntimeException
     */
    public function invalidConfiguration()
    {
        new UnderscoredBundleNamePrefix($this->mockKernel(), [
            'blacklist' => [
                'DoctrineNamingStrategyBundle'
            ],
            'whitelist' => [
                'DoctrineNamingStrategyBundle'
            ]
        ]);
    }

    /**
     * @test
     */
    public function classToTableNameLowercase()
    {
        $strategy = new UnderscoredBundleNamePrefix($this->mockKernel(), [
            'map' => [
                'FooBundle' => 'foo_prefix',
                'Bar' => 'prefix_bar'
            ],
        ]);

        $this->assertSame('foo_prefix_some_entity', $strategy->classToTableName('RunOpenCode\\Bundle\\DoctrineNamingStrategy\\Tests\\Fixtures\\Bundles\\Foo\\Entity\\SomeEntity'));
        $this->assertSame('prefix_bar_other_entity', $strategy->classToTableName('RunOpenCode\\Bundle\\DoctrineNamingStrategy\\Tests\\Fixtures\\Bundles\\Bar\\Entity\\OtherEntity'));
        $this->assertSame('buzz_third_entity', $strategy->classToTableName('RunOpenCode\\Bundle\\DoctrineNamingStrategy\\Tests\\Fixtures\\Bundles\\Buzz\\Entity\\Subfolder\\ThirdEntity'));
    }

    /**
     * @test
     */
    public function classToTableNameUppercase()
    {
        $strategy = new UnderscoredBundleNamePrefix($this->mockKernel(), [
            'map' => [
                'FooBundle' => 'foo_prefix',
                'Bar' => 'prefix_bar'
            ],
            'case' => CASE_UPPER,
        ]);

        $this->assertSame('FOO_PREFIX_SOME_ENTITY', $strategy->classToTableName('RunOpenCode\\Bundle\\DoctrineNamingStrategy\\Tests\\Fixtures\\Bundles\\Foo\\Entity\\SomeEntity'));
        $this->assertSame('PREFIX_BAR_OTHER_ENTITY', $strategy->classToTableName('RunOpenCode\\Bundle\\DoctrineNamingStrategy\\Tests\\Fixtures\\Bundles\\Bar\\Entity\\OtherEntity'));
        $this->assertSame('BUZZ_THIRD_ENTITY', $strategy->classToTableName('RunOpenCode\\Bundle\\DoctrineNamingStrategy\\Tests\\Fixtures\\Bundles\\Buzz\\Entity\\Subfolder\\ThirdEntity'));
    }

    /**
     * @test
     */
    public function propertyToColumnNameLowercase()
    {
        $strategy = new UnderscoredBundleNamePrefix($this->mockKernel());

        $this->assertSame('some_object_property', $strategy->propertyToColumnName('someObjectProperty'));
    }

    /**
     * @test
     */
    public function propertyToColumnNameUppercase()
    {
        $strategy = new UnderscoredBundleNamePrefix($this->mockKernel(), [
            'case' => CASE_UPPER,
        ]);

        $this->assertSame('SOME_OBJECT_PROPERTY', $strategy->propertyToColumnName('someObjectProperty'));
    }

    /**
     * @test
     */
    public function embeddedFieldToColumnNameLowercase()
    {
        $strategy = new UnderscoredBundleNamePrefix($this->mockKernel());

        $this->assertSame('some_object_property_embedded_column_name', $strategy->embeddedFieldToColumnName('someObjectProperty', 'embeddedColumnName'));
    }

    /**
     * @test
     */
    public function embeddedFieldToColumnNameUppercase()
    {
        $strategy = new UnderscoredBundleNamePrefix($this->mockKernel(), [
            'case' => CASE_UPPER,
        ]);

        $this->assertSame('SOME_OBJECT_PROPERTY_EMBEDDED_COLUMN_NAME', $strategy->embeddedFieldToColumnName('someObjectProperty', 'embeddedColumnName'));
    }

    /**
     * @test
     */
    public function referenceColumnName()
    {
        $strategy = new UnderscoredBundleNamePrefix($this->mockKernel());
        $this->assertSame('id', $strategy->referenceColumnName());

        $strategy = new UnderscoredBundleNamePrefix($this->mockKernel(), [
            'case' => CASE_UPPER,
        ]);
        $this->assertSame('ID', $strategy->referenceColumnName());
    }

    /**
     * @test
     */
    public function joinColumnNameLowercase()
    {
        $strategy = new UnderscoredBundleNamePrefix($this->mockKernel());

        $this->assertSame('a_property_id', $strategy->joinColumnName('aProperty'));
    }

    /**
     * @test
     */
    public function joinColumnNameUppercase()
    {
        $strategy = new UnderscoredBundleNamePrefix($this->mockKernel(), [
            'case' => CASE_UPPER,
        ]);

        $this->assertSame('A_PROPERTY_ID', $strategy->joinColumnName('aProperty'));
    }

    /**
     * @test
     */
    public function joinTableNameLowercase()
    {
        $strategy = new UnderscoredBundleNamePrefix($this->mockKernel(), [
            'map' => [
                'FooBundle' => 'foo_prefix',
                'Bar' => 'prefix_bar'
            ],
        ]);

        $this->assertSame('foo_prefix_some_entity_prefix_bar_other_entity', $strategy->joinTableName(
            'RunOpenCode\\Bundle\\DoctrineNamingStrategy\\Tests\\Fixtures\\Bundles\\Foo\\Entity\\SomeEntity',
            'RunOpenCode\\Bundle\\DoctrineNamingStrategy\\Tests\\Fixtures\\Bundles\\Bar\\Entity\\OtherEntity'
        ));
        $this->assertSame('foo_prefix_some_entity_prefix_bar_other_entity_a_property', $strategy->joinTableName(
            'RunOpenCode\\Bundle\\DoctrineNamingStrategy\\Tests\\Fixtures\\Bundles\\Foo\\Entity\\SomeEntity',
            'RunOpenCode\\Bundle\\DoctrineNamingStrategy\\Tests\\Fixtures\\Bundles\\Bar\\Entity\\OtherEntity',
            'aProperty'
        ));
    }

    /**
     * @test
     */
    public function joinTableNameUppercase()
    {
        $strategy = new UnderscoredBundleNamePrefix($this->mockKernel(), [
            'map' => [
                'FooBundle' => 'foo_prefix',
                'Bar' => 'prefix_bar'
            ],
            'case' => CASE_UPPER
        ]);

        $this->assertSame('FOO_PREFIX_SOME_ENTITY_PREFIX_BAR_OTHER_ENTITY', $strategy->joinTableName(
            'RunOpenCode\\Bundle\\DoctrineNamingStrategy\\Tests\\Fixtures\\Bundles\\Foo\\Entity\\SomeEntity',
            'RunOpenCode\\Bundle\\DoctrineNamingStrategy\\Tests\\Fixtures\\Bundles\\Bar\\Entity\\OtherEntity'
        ));
        $this->assertSame('FOO_PREFIX_SOME_ENTITY_PREFIX_BAR_OTHER_ENTITY_A_PROPERTY', $strategy->joinTableName(
            'RunOpenCode\\Bundle\\DoctrineNamingStrategy\\Tests\\Fixtures\\Bundles\\Foo\\Entity\\SomeEntity',
            'RunOpenCode\\Bundle\\DoctrineNamingStrategy\\Tests\\Fixtures\\Bundles\\Bar\\Entity\\OtherEntity',
            'aProperty'
        ));
    }

    /**
     * @test
     */
    public function joinTableNameDisableJoinTableFieldSuffix()
    {
        $strategy = new UnderscoredBundleNamePrefix($this->mockKernel(), [
            'map' => [
                'FooBundle' => 'foo_prefix',
                'Bar' => 'prefix_bar'
            ],
            'joinTableFieldSuffix' => false
        ]);

        $this->assertSame('foo_prefix_some_entity_prefix_bar_other_entity', $strategy->joinTableName(
            'RunOpenCode\\Bundle\\DoctrineNamingStrategy\\Tests\\Fixtures\\Bundles\\Foo\\Entity\\SomeEntity',
            'RunOpenCode\\Bundle\\DoctrineNamingStrategy\\Tests\\Fixtures\\Bundles\\Bar\\Entity\\OtherEntity'
        ));
        $this->assertSame('foo_prefix_some_entity_prefix_bar_other_entity', $strategy->joinTableName(
            'RunOpenCode\\Bundle\\DoctrineNamingStrategy\\Tests\\Fixtures\\Bundles\\Foo\\Entity\\SomeEntity',
            'RunOpenCode\\Bundle\\DoctrineNamingStrategy\\Tests\\Fixtures\\Bundles\\Bar\\Entity\\OtherEntity',
            'aProperty'
        ));
    }

    /**
     * @test
     */
    public function joinKeyColumnNameLowercase()
    {
        $strategy = new UnderscoredBundleNamePrefix($this->mockKernel(), [
            'map' => [
                'FooBundle' => 'foo_prefix',
                'Bar' => 'prefix_bar'
            ],
        ]);

        $this->assertSame('foo_prefix_some_entity_id', $strategy->joinKeyColumnName('RunOpenCode\\Bundle\\DoctrineNamingStrategy\\Tests\\Fixtures\\Bundles\\Foo\\Entity\\SomeEntity'));
        $this->assertSame('prefix_bar_other_entity_id', $strategy->joinKeyColumnName('RunOpenCode\\Bundle\\DoctrineNamingStrategy\\Tests\\Fixtures\\Bundles\\Bar\\Entity\\OtherEntity'));
        $this->assertSame('buzz_third_entity_fk_id', $strategy->joinKeyColumnName('RunOpenCode\\Bundle\\DoctrineNamingStrategy\\Tests\\Fixtures\\Bundles\\Buzz\\Entity\\ThirdEntity', 'fkId'));
    }

    /**
     * @test
     */
    public function joinKeyColumnNameUppercase()
    {
        $strategy = new UnderscoredBundleNamePrefix($this->mockKernel(), [
            'map' => [
                'FooBundle' => 'foo_prefix',
                'Bar' => 'prefix_bar'
            ],
            'case' => CASE_UPPER,
        ]);

        $this->assertSame('FOO_PREFIX_SOME_ENTITY_ID', $strategy->joinKeyColumnName('RunOpenCode\\Bundle\\DoctrineNamingStrategy\\Tests\\Fixtures\\Bundles\\Foo\\Entity\\SomeEntity'));
        $this->assertSame('PREFIX_BAR_OTHER_ENTITY_ID', $strategy->joinKeyColumnName('RunOpenCode\\Bundle\\DoctrineNamingStrategy\\Tests\\Fixtures\\Bundles\\Bar\\Entity\\OtherEntity'));
        $this->assertSame('BUZZ_THIRD_ENTITY_FK_ID', $strategy->joinKeyColumnName('RunOpenCode\\Bundle\\DoctrineNamingStrategy\\Tests\\Fixtures\\Bundles\\Buzz\\Entity\\ThirdEntity', 'fkId'));
    }

    /**
     * @test
     */
    public function blacklisted()
    {
        $strategy = new UnderscoredBundleNamePrefix($this->mockKernel(), [
            'map' => [
                'FooBundle' => 'foo_prefix',
                'Bar' => 'prefix_bar'
            ],
            'blacklist' => [
                'FooBundle'
            ]
        ]);

        $this->assertSame('some_entity', $strategy->classToTableName('RunOpenCode\\Bundle\\DoctrineNamingStrategy\\Tests\\Fixtures\\Bundles\\Foo\\Entity\\SomeEntity'));
        $this->assertSame('prefix_bar_other_entity', $strategy->classToTableName('RunOpenCode\\Bundle\\DoctrineNamingStrategy\\Tests\\Fixtures\\Bundles\\Bar\\Entity\\OtherEntity'));
        $this->assertSame('buzz_third_entity', $strategy->classToTableName('RunOpenCode\\Bundle\\DoctrineNamingStrategy\\Tests\\Fixtures\\Bundles\\Buzz\\Entity\\Subfolder\\ThirdEntity'));

    }

    /**
     * @test
     */
    public function whitelisted()
    {
        $strategy = new UnderscoredBundleNamePrefix($this->mockKernel(), [
            'map' => [
                'FooBundle' => 'foo_prefix',
                'Bar' => 'prefix_bar'
            ],
            'whitelist' => [
                'FooBundle'
            ]
        ]);

        $this->assertSame('foo_prefix_some_entity', $strategy->classToTableName('RunOpenCode\\Bundle\\DoctrineNamingStrategy\\Tests\\Fixtures\\Bundles\\Foo\\Entity\\SomeEntity'));
        $this->assertSame('other_entity', $strategy->classToTableName('RunOpenCode\\Bundle\\DoctrineNamingStrategy\\Tests\\Fixtures\\Bundles\\Bar\\Entity\\OtherEntity'));
        $this->assertSame('third_entity', $strategy->classToTableName('RunOpenCode\\Bundle\\DoctrineNamingStrategy\\Tests\\Fixtures\\Bundles\\Buzz\\Entity\\Subfolder\\ThirdEntity'));
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
