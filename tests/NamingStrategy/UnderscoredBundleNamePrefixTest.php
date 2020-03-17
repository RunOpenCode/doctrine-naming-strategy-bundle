<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\DoctrineNamingStrategy\Tests\NamingStrategy;

use PHPUnit\Framework\TestCase;
use RunOpenCode\Bundle\DoctrineNamingStrategy\Exception\RuntimeException;
use RunOpenCode\Bundle\DoctrineNamingStrategy\NamingStrategy\UnderscoredBundleNamePrefix;
use RunOpenCode\Bundle\DoctrineNamingStrategy\Tests\Fixtures\Bundles\Bar\BarBundle;
use RunOpenCode\Bundle\DoctrineNamingStrategy\Tests\Fixtures\Bundles\Buzz\BuzzBundle;
use RunOpenCode\Bundle\DoctrineNamingStrategy\Tests\Fixtures\Bundles\Foo\FooBundle;
use Symfony\Component\HttpKernel\Kernel;

final class UnderscoredBundleNamePrefixTest extends TestCase
{
    /**
     * @test
     */
    public function invalidConfiguration(): void
    {
        $this->expectException(RuntimeException::class);

        new UnderscoredBundleNamePrefix($this->mockKernel(), [
            'blacklist' => [
                'DoctrineNamingStrategyBundle',
            ],
            'whitelist' => [
                'DoctrineNamingStrategyBundle',
            ],
        ]);
    }

    /**
     * @test
     */
    public function classToTableNameLowercase(): void
    {
        $strategy = new UnderscoredBundleNamePrefix($this->mockKernel(), [
            'map' => [
                'FooBundle' => 'foo_prefix',
                'Bar'       => 'prefix_bar',
            ],
        ]);

        $this->assertSame('foo_prefix_some_entity', $strategy->classToTableName('RunOpenCode\\Bundle\\DoctrineNamingStrategy\\Tests\\Fixtures\\Bundles\\Foo\\Entity\\SomeEntity'));
        $this->assertSame('prefix_bar_other_entity', $strategy->classToTableName('RunOpenCode\\Bundle\\DoctrineNamingStrategy\\Tests\\Fixtures\\Bundles\\Bar\\Entity\\OtherEntity'));
        $this->assertSame('buzz_third_entity', $strategy->classToTableName('RunOpenCode\\Bundle\\DoctrineNamingStrategy\\Tests\\Fixtures\\Bundles\\Buzz\\Entity\\Subfolder\\ThirdEntity'));
    }

    /**
     * @test
     */
    public function classToTableNameUppercase(): void
    {
        $strategy = new UnderscoredBundleNamePrefix($this->mockKernel(), [
            'map'  => [
                'FooBundle' => 'foo_prefix',
                'Bar'       => 'prefix_bar',
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
    public function propertyToColumnNameLowercase(): void
    {
        $strategy = new UnderscoredBundleNamePrefix($this->mockKernel());

        $this->assertSame('some_object_property', $strategy->propertyToColumnName('someObjectProperty'));
    }

    /**
     * @test
     */
    public function propertyToColumnNameUppercase(): void
    {
        $strategy = new UnderscoredBundleNamePrefix($this->mockKernel(), [
            'case' => CASE_UPPER,
        ]);

        $this->assertSame('SOME_OBJECT_PROPERTY', $strategy->propertyToColumnName('someObjectProperty'));
    }

    /**
     * @test
     */
    public function embeddedFieldToColumnNameLowercase(): void
    {
        $strategy = new UnderscoredBundleNamePrefix($this->mockKernel());

        $this->assertSame('some_object_property_embedded_column_name', $strategy->embeddedFieldToColumnName('someObjectProperty', 'embeddedColumnName'));
    }

    /**
     * @test
     */
    public function embeddedFieldToColumnNameUppercase(): void
    {
        $strategy = new UnderscoredBundleNamePrefix($this->mockKernel(), [
            'case' => CASE_UPPER,
        ]);

        $this->assertSame('SOME_OBJECT_PROPERTY_EMBEDDED_COLUMN_NAME', $strategy->embeddedFieldToColumnName('someObjectProperty', 'embeddedColumnName'));
    }

    /**
     * @test
     */
    public function referenceColumnName(): void
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
    public function joinColumnNameLowercase(): void
    {
        $strategy = new UnderscoredBundleNamePrefix($this->mockKernel());

        $this->assertSame('a_property_id', $strategy->joinColumnName('aProperty'));
    }

    /**
     * @test
     */
    public function joinColumnNameUppercase(): void
    {
        $strategy = new UnderscoredBundleNamePrefix($this->mockKernel(), [
            'case' => CASE_UPPER,
        ]);

        $this->assertSame('A_PROPERTY_ID', $strategy->joinColumnName('aProperty'));
    }

    /**
     * @test
     */
    public function joinTableNameLowercase(): void
    {
        $strategy = new UnderscoredBundleNamePrefix($this->mockKernel(), [
            'map' => [
                'FooBundle' => 'foo_prefix',
                'Bar'       => 'prefix_bar',
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
    public function joinTableNameUppercase(): void
    {
        $strategy = new UnderscoredBundleNamePrefix($this->mockKernel(), [
            'map'  => [
                'FooBundle' => 'foo_prefix',
                'Bar'       => 'prefix_bar',
            ],
            'case' => CASE_UPPER,
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
    public function joinTableNameDisableJoinTableFieldSuffix(): void
    {
        $strategy = new UnderscoredBundleNamePrefix($this->mockKernel(), [
            'map'                     => [
                'FooBundle' => 'foo_prefix',
                'Bar'       => 'prefix_bar',
            ],
            'join_table_field_suffix' => false,
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
    public function joinKeyColumnNameLowercase(): void
    {
        $strategy = new UnderscoredBundleNamePrefix($this->mockKernel(), [
            'map' => [
                'FooBundle' => 'foo_prefix',
                'Bar'       => 'prefix_bar',
            ],
        ]);

        $this->assertSame('foo_prefix_some_entity_id', $strategy->joinKeyColumnName('RunOpenCode\\Bundle\\DoctrineNamingStrategy\\Tests\\Fixtures\\Bundles\\Foo\\Entity\\SomeEntity'));
        $this->assertSame('prefix_bar_other_entity_id', $strategy->joinKeyColumnName('RunOpenCode\\Bundle\\DoctrineNamingStrategy\\Tests\\Fixtures\\Bundles\\Bar\\Entity\\OtherEntity'));
        $this->assertSame('buzz_third_entity_fk_id', $strategy->joinKeyColumnName('RunOpenCode\\Bundle\\DoctrineNamingStrategy\\Tests\\Fixtures\\Bundles\\Buzz\\Entity\\ThirdEntity', 'fkId'));
    }

    /**
     * @test
     */
    public function joinKeyColumnNameUppercase(): void
    {
        $strategy = new UnderscoredBundleNamePrefix($this->mockKernel(), [
            'map'  => [
                'FooBundle' => 'foo_prefix',
                'Bar'       => 'prefix_bar',
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
    public function blacklisted(): void
    {
        $strategy = new UnderscoredBundleNamePrefix($this->mockKernel(), [
            'map'       => [
                'FooBundle' => 'foo_prefix',
                'Bar'       => 'prefix_bar',
            ],
            'blacklist' => [
                'FooBundle',
            ],
        ]);

        $this->assertSame('some_entity', $strategy->classToTableName('RunOpenCode\\Bundle\\DoctrineNamingStrategy\\Tests\\Fixtures\\Bundles\\Foo\\Entity\\SomeEntity'));
        $this->assertSame('prefix_bar_other_entity', $strategy->classToTableName('RunOpenCode\\Bundle\\DoctrineNamingStrategy\\Tests\\Fixtures\\Bundles\\Bar\\Entity\\OtherEntity'));
        $this->assertSame('buzz_third_entity', $strategy->classToTableName('RunOpenCode\\Bundle\\DoctrineNamingStrategy\\Tests\\Fixtures\\Bundles\\Buzz\\Entity\\Subfolder\\ThirdEntity'));
    }

    /**
     * @test
     */
    public function whitelisted(): void
    {
        $strategy = new UnderscoredBundleNamePrefix($this->mockKernel(), [
            'map'       => [
                'FooBundle' => 'foo_prefix',
                'Bar'       => 'prefix_bar',
            ],
            'whitelist' => [
                'FooBundle',
            ],
        ]);

        $this->assertSame('foo_prefix_some_entity', $strategy->classToTableName('RunOpenCode\\Bundle\\DoctrineNamingStrategy\\Tests\\Fixtures\\Bundles\\Foo\\Entity\\SomeEntity'));
        $this->assertSame('other_entity', $strategy->classToTableName('RunOpenCode\\Bundle\\DoctrineNamingStrategy\\Tests\\Fixtures\\Bundles\\Bar\\Entity\\OtherEntity'));
        $this->assertSame('third_entity', $strategy->classToTableName('RunOpenCode\\Bundle\\DoctrineNamingStrategy\\Tests\\Fixtures\\Bundles\\Buzz\\Entity\\Subfolder\\ThirdEntity'));
    }

    private function mockKernel(): Kernel
    {
        $stub = $this->getMockBuilder(Kernel::class)->disableOriginalConstructor()->getMock();

        $stub->method('getBundles')
             ->willReturn([
                 new FooBundle(),
                 new BarBundle(),
                 new BuzzBundle(),
             ]);

        /** @var Kernel $stub */
        return $stub;
    }
}
