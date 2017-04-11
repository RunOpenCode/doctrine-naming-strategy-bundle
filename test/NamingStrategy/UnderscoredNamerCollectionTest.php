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
     * @test
     */
    public function classToTableName()
    {
        $namer = $this->getNamerCollection();

        $this->assertSame('name', $namer->classToTableName('Some\\Unregistered\\Name'));
        $this->assertSame('FIRST_CLASS_PREFIX_NAME', $namer->classToTableName('RunOpenCode\\Bundle\\TestNamespace\\Entity\\Name'));
        $this->assertSame('foo_bundle_prefix_some_entity', $namer->classToTableName('RunOpenCode\\Bundle\\DoctrineNamingStrategy\\Tests\\Fixtures\\Bundles\\Foo\\Entity\\SomeEntity'));
    }

    /**
     * @test
     */
    public function propertyToColumnName()
    {
        $namer = $this->getNamerCollection();

        $this->assertSame('test_property', $namer->propertyToColumnName('testProperty'));
        $this->assertSame('test_property', $namer->propertyToColumnName('testProperty', 'Some\\Unregistered\\Name'));
        $this->assertSame('TEST_PROPERTY', $namer->propertyToColumnName('testProperty','RunOpenCode\\Bundle\\TestNamespace\\Entity\\Name'));
    }

    /**
     * @test
     */
    public function embeddedFieldToColumnName()
    {
        $namer = $this->getNamerCollection();

        $this->assertSame('test_property_testEmbeddedProperty', $namer->embeddedFieldToColumnName(
            'testProperty',
            'testEmbeddedProperty'
        ));

        $this->assertSame('TEST_PROPERTY_TEST_EMBEDDED_PROPERTY', $namer->embeddedFieldToColumnName(
            'testProperty',
            'testEmbeddedProperty',
            'RunOpenCode\\Bundle\\TestNamespace\\Entity\\Name'
        ));

        $this->assertSame('test_property_test_embedded_property', $namer->embeddedFieldToColumnName(
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
        $this->assertSame('id', $this->getNamerCollection()->referenceColumnName());
    }

    /**
     * @test
     */
    public function joinColumnName()
    {
        $namer = $this->getNamerCollection();

        $this->assertSame('test_property_id', $namer->joinColumnName(
            'testProperty'
        ));

        $this->assertSame('TEST_PROPERTY_ID', $namer->joinColumnName(
            'testProperty',
            'RunOpenCode\\Bundle\\TestNamespace\\Entity\\Name'
        ));
    }



    private function getNamerCollection($joinTableFieldSuffix = true)
    {
        $namingStrategy = new UnderscoredNamerCollection(new UnderscoreNamingStrategy(), [], [
            'joinTableFieldSuffix' => $joinTableFieldSuffix,
        ]);

        $bundleLowercase = new UnderscoredBundleNamePrefix($this->mockKernel(), [
            'map' => [
                'FooBundle' => 'foo_bundle_prefix',
            ],
            'joinTableFieldSuffix' => false,
            'case' => CASE_LOWER,
        ]);

        $bundleUppercase = new UnderscoredBundleNamePrefix($this->mockKernel(), [
            'map' => [
                'Bar' => 'bar_bundle_prefix',
            ],
            'joinTableFieldSuffix' => true,
            'case' => CASE_UPPER,
        ]);

        $fqcnLowercase = new UnderscoredClassNamespacePrefix([
            'map' => [
                'RunOpenCode\\Bundle\\TestNamespace\\Other' => 'second_class_prefix',
            ],
            'joinTableFieldSuffix' => false,
            'case' => CASE_LOWER,
        ]);

        $fqcnUppercase = new UnderscoredClassNamespacePrefix([
            'map' => [
                'RunOpenCode\\Bundle\\TestNamespace\\Entity' => 'first_class_prefix',
            ],
            'case' => CASE_UPPER,
            'joinTableFieldSuffix' => true,
        ]);

        $namingStrategy
            ->registerNamingStrategy($bundleLowercase)
            ->registerNamingStrategy($bundleUppercase)
            ->registerNamingStrategy($fqcnLowercase)
            ->registerNamingStrategy($fqcnUppercase);

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

