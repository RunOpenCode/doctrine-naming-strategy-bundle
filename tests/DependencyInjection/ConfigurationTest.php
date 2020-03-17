<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\DoctrineNamingStrategy\Tests\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionConfigurationTestCase;
use RunOpenCode\Bundle\DoctrineNamingStrategy\DependencyInjection\Configuration;
use RunOpenCode\Bundle\DoctrineNamingStrategy\DependencyInjection\Extension;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

final class ConfigurationTest extends AbstractExtensionConfigurationTestCase
{
    /**
     * @test
     */
    public function itHasReasonableDefaults(): void
    {
        $this->assertProcessedConfigurationEquals([
            'underscored_bundle_prefix' => [
                'case' => 'lowercase',
                'join_table_field_suffix' => true,
                'map' => [],
                'whitelist' => [],
                'blacklist' => [],
            ],
            'underscored_class_namespace_prefix' => [
                'case' => 'lowercase',
                'join_table_field_suffix' => true,
                'map' => [],
                'whitelist' => [],
                'blacklist' => [],
            ],
            'underscored_namer_collection' => [
                'default' => 'doctrine.orm.naming_strategy.underscore',
                'join_table_field_suffix' => true,
                'namers' => [
                    'runopencode.doctrine.orm.naming_strategy.underscored_class_namespace_prefix',
                    'runopencode.doctrine.orm.naming_strategy.underscored_bundle_prefix',
                ],
            ],
        ], [
            __DIR__ . '/../Fixtures/config/empty.xml'
        ]);
    }

    /**
     * @test
     */
    public function itCanBeProperlyConfigured(): void
    {
        $this->assertProcessedConfigurationEquals([
            'underscored_bundle_prefix' => [
                'case' => 'uppercase',
                'join_table_field_suffix' => false,
                'map' => [
                    'SomeBundle' => 'some_prefix',
                    'SomeOtherBundle' => 'some_other_prefix',
                ],
                'whitelist' => [],
                'blacklist' => [
                    'ExcludedBundle',
                ],
            ],
            'underscored_class_namespace_prefix' => [
                'case' => 'uppercase',
                'join_table_field_suffix' => false,
                'map' => [
                    'Some\Namespace' => 'some_prefix',
                    'Some\Other\Namespace' => 'some_other_prefix',
                ],
                'whitelist' => [
                    'Some\Namespace',
                    'Some\Other\Namespace',
                ],
                'blacklist' => [],
            ],
            'underscored_namer_collection' => [
                'default' => 'default_namer',
                'join_table_field_suffix' => false,
                'namers' => [
                    'first_namer',
                ],
            ],
        ], [
            __DIR__ . '/../Fixtures/config/full.xml'
        ]);
    }

    /**
     * @test
     */
    public function itCanBeProperlyConfiguredWithYaml(): void
    {
        $this->assertProcessedConfigurationEquals([
            'underscored_bundle_prefix' => [
                'case' => 'uppercase',
                'join_table_field_suffix' => false,
                'map' => [
                    'MyLongNameOfTheBundle' => 'my_prefix',
                    'MyOtherLongNameOfTheBundle' => 'my_prefix_2',
                ],
                'whitelist' => [],
                'blacklist' => [
                    'DoNotPrefixThisBundle',
                ],
            ],
            'underscored_class_namespace_prefix' => [
                'case' => 'uppercase',
                'join_table_field_suffix' => false,
                'map' => [
                    'My\Class\Namespace\Entity' => 'my_prefix',
                ],
                'whitelist' => [
                    'My\Class\Namespace\Entity\ThisShouldNotBeSkipped',
                    'My\Class\Namespace\Entity\ThisShouldNotBeSkippedAsWell',
                ],
                'blacklist' => [],
            ],
            'underscored_namer_collection' => [
                'default' => 'default_namer',
                'join_table_field_suffix' => false,
                'namers' => [
                    'a_namer',
                ],
            ],
        ], [
            __DIR__ . '/../Fixtures/config/full.yml'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    final protected function getContainerExtension(): ExtensionInterface
    {
        return new Extension();
    }

    /**
     * {@inheritdoc}
     */
    final protected function getConfiguration(): ConfigurationInterface
    {
        return new Configuration();
    }
}
