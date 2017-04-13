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
use RunOpenCode\Bundle\DoctrineNamingStrategy\NamingStrategy\UnderscoredNamerCollection;
use RunOpenCode\Bundle\DoctrineNamingStrategy\NamingStrategy\UnderscoredBundleNamePrefix;
use RunOpenCode\Bundle\DoctrineNamingStrategy\NamingStrategy\UnderscoredClassNamespacePrefix;
use RunOpenCode\Bundle\DoctrineNamingStrategy\Tests\Fixtures\Bundles\Bar\BarBundle;
use RunOpenCode\Bundle\DoctrineNamingStrategy\Tests\Fixtures\Bundles\Buzz\BuzzBundle;
use RunOpenCode\Bundle\DoctrineNamingStrategy\Tests\Fixtures\Bundles\Foo\FooBundle;

class UnderscoredNamerCollectionTest extends TestCase
{
    /**
     * @var UnderscoredNamerCollection
     */
    private $namer;

    public function setUp()
    {
        $this->namer = $this->getNamerCollection();
    }

    /**
     * @test
     * @expectedException \RunOpenCode\Bundle\DoctrineNamingStrategy\Exception\InvalidArgumentException
     */
    public function defaultNamingStrategyCanNotBeRegisteredAsConcurentOne()
    {
        $namingStrategy = new UnderscoredNamerCollection($default = new UnderscoreNamingStrategy());
        $namingStrategy->registerNamingStrategy($default);
    }

    /**
     * @test
     */
    public function classToTableName()
    {
        $this->assertSame('name', $this->namer->classToTableName('Some\\Unregistered\\Name'));
        $this->assertSame('FIRST_CLASS_PREFIX_NAME', $this->namer->classToTableName('RunOpenCode\\Bundle\\TestNamespace\\Entity\\Name'));
        $this->assertSame('foo_bundle_prefix_some_entity', $this->namer->classToTableName('RunOpenCode\\Bundle\\DoctrineNamingStrategy\\Tests\\Fixtures\\Bundles\\Foo\\Entity\\SomeEntity'));
    }

    /**
     * @test
     */
    public function propertyToColumnName()
    {
        $this->assertSame('test_property', $this->namer->propertyToColumnName('testProperty'));
        $this->assertSame('test_property', $this->namer->propertyToColumnName('testProperty', 'Some\\Unregistered\\Name'));
        $this->assertSame('TEST_PROPERTY', $this->namer->propertyToColumnName('testProperty','RunOpenCode\\Bundle\\TestNamespace\\Entity\\Name'));
    }

    /**
     * @test
     */
    public function embeddedFieldToColumnName()
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
    public function referenceColumnName()
    {
        $this->assertSame('id', $this->namer->referenceColumnName());
    }

    /**
     * @test
     */
    public function joinColumnName()
    {
        $this->assertSame('test_property_id', $this->namer->joinColumnName(
            'testProperty'
        ));

        $this->assertSame('TEST_PROPERTY_ID', $this->namer->joinColumnName(
            'testProperty',
            'RunOpenCode\\Bundle\\TestNamespace\\Entity\\Name'
        ));
    }

    /**
     * @test
     */
    public function joinTableName()
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
    public function joinKeyColumnName()
    {
        $namer = $this->getNamerCollection();

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

    private function getNamerCollection()
    {
        $bundleLowercase = new UnderscoredBundleNamePrefix($this->mockKernel(), [
            'map' => [
                'FooBundle' => 'foo_bundle_prefix',
            ],
            'join_table_field_suffix' => false,
            'case' => CASE_LOWER,
        ]);

        $bundleUppercase = new UnderscoredBundleNamePrefix($this->mockKernel(), [
            'map' => [
                'Bar' => 'bar_bundle_prefix',
            ],
            'join_table_field_suffix' => true,
            'case' => CASE_UPPER,
        ]);

        $fqcnLowercase = new UnderscoredClassNamespacePrefix([
            'map' => [
                'RunOpenCode\\Bundle\\TestNamespace\\Other' => 'second_class_prefix',
            ],
            'join_table_field_suffix' => false,
            'case' => CASE_LOWER,
        ]);

        $fqcnUppercase = new UnderscoredClassNamespacePrefix([
            'map' => [
                'RunOpenCode\\Bundle\\TestNamespace\\Entity' => 'first_class_prefix',
            ],
            'case' => CASE_UPPER,
            'join_table_field_suffix' => true,
        ]);

        $namingStrategy = new UnderscoredNamerCollection(new UnderscoreNamingStrategy(), [
            $bundleLowercase,
            $bundleUppercase,
            $fqcnLowercase,
            $fqcnUppercase
        ]);

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

