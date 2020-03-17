<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\DoctrineNamingStrategy\Tests\NamingStrategy;

use Doctrine\ORM\Mapping\UnderscoreNamingStrategy;
use PHPUnit\Framework\TestCase;
use RunOpenCode\Bundle\DoctrineNamingStrategy\Exception\InvalidArgumentException;
use RunOpenCode\Bundle\DoctrineNamingStrategy\NamingStrategy\UnderscoredNamerCollection;
use RunOpenCode\Bundle\DoctrineNamingStrategy\NamingStrategy\UnderscoredBundleNamePrefix;
use RunOpenCode\Bundle\DoctrineNamingStrategy\NamingStrategy\UnderscoredClassNamespacePrefix;
use RunOpenCode\Bundle\DoctrineNamingStrategy\Tests\Fixtures\Bundles\Bar\BarBundle;
use RunOpenCode\Bundle\DoctrineNamingStrategy\Tests\Fixtures\Bundles\Buzz\BuzzBundle;
use RunOpenCode\Bundle\DoctrineNamingStrategy\Tests\Fixtures\Bundles\Foo\FooBundle;
use Symfony\Component\HttpKernel\Kernel;

final class UnderscoredNamerCollectionTest extends TestCase
{
    private UnderscoredNamerCollection $namer;

    public function setUp(): void
    {
        $this->namer = $this->getNamerCollection();
    }

    /**
     * @test
     */
    public function defaultNamingStrategyCanNotBeRegisteredAsConcurrentOne(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $namingStrategy = new UnderscoredNamerCollection($default = new UnderscoreNamingStrategy());

        $namingStrategy->registerNamingStrategy($default);
    }

    /**
     * @test
     */
    public function classToTableName(): void
    {
        $this->assertSame('name', $this->namer->classToTableName('Some\\Unregistered\\Name'));
        $this->assertSame('FIRST_CLASS_PREFIX_NAME', $this->namer->classToTableName('RunOpenCode\\Bundle\\TestNamespace\\Entity\\Name'));
        $this->assertSame('foo_bundle_prefix_some_entity', $this->namer->classToTableName('RunOpenCode\\Bundle\\DoctrineNamingStrategy\\Tests\\Fixtures\\Bundles\\Foo\\Entity\\SomeEntity'));
    }

    /**
     * @test
     */
    public function propertyToColumnName(): void
    {
        $this->assertSame('test_property', $this->namer->propertyToColumnName('testProperty'));
        $this->assertSame('test_property', $this->namer->propertyToColumnName('testProperty', 'Some\\Unregistered\\Name'));
        $this->assertSame('TEST_PROPERTY', $this->namer->propertyToColumnName('testProperty', 'RunOpenCode\\Bundle\\TestNamespace\\Entity\\Name'));
    }

    /**
     * @test
     */
    public function embeddedFieldToColumnName(): void
    {
        $this->assertSame('test_property_testEmbeddedProperty', $this->namer->embeddedFieldToColumnName(
            'testProperty',
            'testEmbeddedProperty'
        ));

        $this->assertSame('TEST_PROPERTY_TEST_EMBEDDED_PROPERTY', $this->namer->embeddedFieldToColumnName(
            'testProperty',
            'testEmbeddedProperty',
            'RunOpenCode\\Bundle\\TestNamespace\\Entity\\Name'
        ));

        $this->assertSame('test_property_test_embedded_property', $this->namer->embeddedFieldToColumnName(
            'testProperty',
            'testEmbeddedProperty',
            'RunOpenCode\\Bundle\\TestNamespace\\Entity\\Name',
            'RunOpenCode\\Bundle\\DoctrineNamingStrategy\\Tests\\Fixtures\\Bundles\\Foo\\Entity\\SomeEntity'
        ));
    }

    /**
     * @test
     */
    public function referenceColumnName(): void
    {
        $this->assertSame('id', $this->namer->referenceColumnName());
    }

    /**
     * @test
     */
    public function joinColumnName(): void
    {
        $this->assertSame('test_property_id', $this->namer->joinColumnName(
            'testProperty'
        ));
    }

    /**
     * @test
     */
    public function joinTableName(): void
    {
        $this->assertSame('FIRST_CLASS_PREFIX_NAME_foo_bundle_prefix_some_entity', $this->namer->joinTableName(
            'RunOpenCode\\Bundle\\TestNamespace\\Entity\\Name',
            'RunOpenCode\\Bundle\\DoctrineNamingStrategy\\Tests\\Fixtures\\Bundles\\Foo\\Entity\\SomeEntity'
        ));

        $this->assertSame('FIRST_CLASS_PREFIX_NAME_foo_bundle_prefix_some_entity_A_PROPERTY', $this->namer->joinTableName(
            'RunOpenCode\\Bundle\\TestNamespace\\Entity\\Name',
            'RunOpenCode\\Bundle\\DoctrineNamingStrategy\\Tests\\Fixtures\\Bundles\\Foo\\Entity\\SomeEntity',
            'aProperty'
        ));
    }

    /**
     * @test
     */
    public function joinKeyColumnName(): void
    {
        $this->assertSame('foo_bundle_prefix_some_entity_id', $this->namer->joinKeyColumnName(
            'RunOpenCode\\Bundle\\DoctrineNamingStrategy\\Tests\\Fixtures\\Bundles\\Foo\\Entity\\SomeEntity'
        ));

        $this->assertSame('FIRST_CLASS_PREFIX_NAME_ID', $this->namer->joinKeyColumnName(
            'RunOpenCode\\Bundle\\TestNamespace\\Entity\\Name'
        ));

        $this->assertSame('foo_bundle_prefix_some_entity_key_column', $this->namer->joinKeyColumnName(
            'RunOpenCode\\Bundle\\DoctrineNamingStrategy\\Tests\\Fixtures\\Bundles\\Foo\\Entity\\SomeEntity',
            'keyColumn'
        ));

        $this->assertSame('FIRST_CLASS_PREFIX_NAME_KEY_COLUMN', $this->namer->joinKeyColumnName(
            'RunOpenCode\\Bundle\\TestNamespace\\Entity\\Name',
            'keyColumn'
        ));
    }

    private function getNamerCollection(): UnderscoredNamerCollection
    {
        $bundleLowercase = new UnderscoredBundleNamePrefix($this->mockKernel(), [
            'map'                     => [
                'FooBundle' => 'foo_bundle_prefix',
            ],
            'join_table_field_suffix' => false,
            'case'                    => CASE_LOWER,
        ]);

        $bundleUppercase = new UnderscoredBundleNamePrefix($this->mockKernel(), [
            'map'                     => [
                'Bar' => 'bar_bundle_prefix',
            ],
            'join_table_field_suffix' => true,
            'case'                    => CASE_UPPER,
        ]);

        $fqcnLowercase = new UnderscoredClassNamespacePrefix([
            'map'                     => [
                'RunOpenCode\\Bundle\\TestNamespace\\Other' => 'second_class_prefix',
            ],
            'join_table_field_suffix' => false,
            'case'                    => CASE_LOWER,
        ]);

        $fqcnUppercase = new UnderscoredClassNamespacePrefix([
            'map'                     => [
                'RunOpenCode\\Bundle\\TestNamespace\\Entity' => 'first_class_prefix',
            ],
            'case'                    => CASE_UPPER,
            'join_table_field_suffix' => true,
        ]);

        return new UnderscoredNamerCollection(new UnderscoreNamingStrategy(), [
            $bundleLowercase,
            $bundleUppercase,
            $fqcnLowercase,
            $fqcnUppercase,
        ]);
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
