<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\DoctrineNamingStrategy\Tests\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use RunOpenCode\Bundle\DoctrineNamingStrategy\DependencyInjection\Extension;

final class ExtensionTest extends AbstractExtensionTestCase
{
    /**
     * @test
     */
    public function configureUnderscoredBundlePrefixNamer(): void
    {
        foreach (['lowercase', 'uppercase'] as $case) {
            $configuration = array(
                'case' => $case,
                'map' => array(
                    'MyLongNameOfTheBundle' => 'my_prefix',
                    'MyOtherLongNameOfTheBundle' => 'my_prefix_2'
                ),
                'join_table_field_suffix' => true,
                'blacklist' =>
                    array(
                        'DoNotPrefixThisBundle'
                    ),
                'whitelist' => array()
            );

            $this->load(array('underscored_bundle_prefix' => $configuration));

            $this->assertContainerBuilderHasService('runopencode.doctrine.orm.naming_strategy.underscored_bundle_prefix');

            $configuration['case'] = ('uppercase' === $case) ? CASE_UPPER : CASE_LOWER;

            $this->assertContainerBuilderHasServiceDefinitionWithArgument(
                'runopencode.doctrine.orm.naming_strategy.underscored_bundle_prefix',
                1,
                $configuration
            );
        }
    }

    /**
     * @test
     */
    public function configureUnderscoredClassNamespacePrefixNamer(): void
    {
        foreach (['lowercase', 'uppercase'] as $case) {
            $configuration = array(
                'case' => $case,
                'map' => array(
                    'My\Class\Namespace\Entity' => 'my_prefix'
                ),
                'join_table_field_suffix' => true,
                'blacklist' =>
                    array(
                        'My\Class\Namespace\Entity\ThisShouldBeSkipped',
                        'My\Class\Namespace\Entity\ThisShouldBeSkippedAsWell'
                    ),
                'whitelist' => array()
            );

            $this->load(array('underscored_class_namespace_prefix' => $configuration));

            $this->assertContainerBuilderHasService('runopencode.doctrine.orm.naming_strategy.underscored_class_namespace_prefix');

            $configuration['case'] = ('uppercase' === $case) ? CASE_UPPER : CASE_LOWER;

            $this->assertContainerBuilderHasServiceDefinitionWithArgument(
                'runopencode.doctrine.orm.naming_strategy.underscored_class_namespace_prefix',
                0,
                $configuration
            );
        }
    }

    /**
     * @test
     */
    public function configureNamerCollection(): void
    {
        $configuration = array(
            'default' => 'doctrine.orm.naming_strategy.underscore',
            'namers' => array(
                'runopencode.doctrine.orm.naming_strategy.underscored_class_namespace_prefix',
                'runopencode.doctrine.orm.naming_strategy.underscored_bundle_prefix'
            ),
            'join_table_field_suffix' => true
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
                'join_table_field_suffix' => $configuration['join_table_field_suffix']
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    final protected function getContainerExtensions(): array
    {
        return array(
            new Extension()
        );
    }
}
