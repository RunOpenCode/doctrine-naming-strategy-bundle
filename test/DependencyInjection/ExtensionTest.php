<?php
/*
 * This file is part of the Doctrine Naming Strategy Bundle, an RunOpenCode project.
 *
 * (c) 2017 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\Bundle\DoctrineNamingStrategy\Tests\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use RunOpenCode\Bundle\DoctrineNamingStrategy\DependencyInjection\Extension;
use RunOpenCode\Bundle\DoctrineNamingStrategy\NamingStrategy\NamerCollection;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

class ExtensionTest extends AbstractExtensionTestCase
{
    /**
     * @test
     */
    public function configureUnderscoredBundlePrefixNamer()
    {
        $configuration = array(
            'case' => 'lowercase',
            'map' => array(
                'MyLongNameOfTheBundle' => 'my_prefix',
                'MyOtherLongNameOfTheBundle' => 'my_prefix_2'
            ),
            'joinTableFieldSuffix' => true,
            'blacklist' =>
                array(
                    'DoNotPrefixThisBundle'
                ),
            'whitelist' => array()
        );

        $this->load(array('underscored_bundle_prefix' => $configuration));

        $this->assertContainerBuilderHasService('runopencode.doctrine.orm.naming_strategy.underscored_bundle_prefix');

        $configuration['case'] = CASE_LOWER;

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'runopencode.doctrine.orm.naming_strategy.underscored_bundle_prefix',
            1,
            $configuration
        );
    }

    /**
     * @test
     */
    public function configureUnderscoredClassNamespacePrefixNamer()
    {
        $configuration = array(
            'case' => 'lowercase',
            'map' => array(
                'My\Class\Namespace\Entity' => 'my_prefix'
            ),
            'joinTableFieldSuffix' => true,
            'blacklist' =>
                array(
                    'My\Class\Namespace\Entity\ThisShouldBeSkipped',
                    'My\Class\Namespace\Entity\ThisShouldBeSkippedAsWell'
                ),
            'whitelist' => array()
        );

        $this->load(array('underscored_class_namespace_prefix' => $configuration));

        $this->assertContainerBuilderHasService('runopencode.doctrine.orm.naming_strategy.underscored_class_namespace_prefix');

        $configuration['case'] = CASE_LOWER;

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'runopencode.doctrine.orm.naming_strategy.underscored_class_namespace_prefix',
            0,
            $configuration
        );
    }

    /**
     * @test
     */
    public function configureNamerCollection()
    {
        $configuration = array(
            'default' => 'doctrine.orm.naming_strategy.underscore',
            'namers' => array(
                'runopencode.doctrine.orm.naming_strategy.underscored_class_namespace_prefix',
                'runopencode.doctrine.orm.naming_strategy.underscored_bundle_prefix'
            ),
            'joinTableFieldSuffix' => true
        );

        $this->load(array('underscored_namer_collection' => $configuration));

        $this->assertContainerBuilderHasService('runopencode.doctrine.orm.naming_strategy.underscored_namer_collection');

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'runopencode.doctrine.orm.naming_strategy.underscored_namer_collection',
            1,
            $configuration['namers']
        );

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'runopencode.doctrine.orm.naming_strategy.underscored_namer_collection',
            2,
            array(
                'joinTableFieldSuffix' => $configuration['joinTableFieldSuffix']
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getContainerExtensions()
    {
        return array(
            new Extension()
        );
    }
}