<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\DoctrineNamingStrategy\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('runopencode_doctrine_naming_strategy');
        $rootNode    = $treeBuilder->getRootNode();

        /**
         * @psalm-suppress all
         */
        $rootNode
            ->children()
                ->append($this->getUnderscoredBundlePrefixDefinition())
                ->append($this->getUnderscoredClassNamespacePrefixDefinition())
                ->append($this->getNamerCollectionDefinition())
            ->end()
        ->end();

        return $treeBuilder;
    }

    /**
     * Configure underscored bundle prefix naming strategy
     */
    private function getUnderscoredBundlePrefixDefinition(): ArrayNodeDefinition
    {
        $node = new ArrayNodeDefinition('underscored_bundle_prefix');

        /**
         * @psalm-suppress all
         */
        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->enumNode('case')
                    ->info('Which case to use, lowercase or uppercase. Default is lowercase.')
                    ->values(array('lowercase', 'uppercase'))
                    ->defaultValue('lowercase')
                ->end()
                ->booleanNode('join_table_field_suffix')
                    ->defaultTrue()
                    ->info('Join table will get field name suffix enabling you to have multiple many-to-many relations without stating explicitly table names.')
                ->end()
                ->arrayNode('map')
                    ->info('Map of short bundle names and prefixes, if you do not want to use full bundle name in prefix. Useful when bundle name is too long, considering that, per example, MySQL has 60 chars table name limit.')
                    ->useAttributeAsKey('bundle')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('whitelist')
                    ->beforeNormalization()
                        ->ifString()
                        ->then(static function ($value) {
                            return [$value];
                        })
                        ->end()
                    ->info('Define for which bundles to apply prefixes.')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('blacklist')
                    ->beforeNormalization()
                        ->ifString()
                        ->then(static function ($value) {
                            return [$value];
                        })
                    ->end()
                    ->info('Define for which bundles not to apply prefixes.')
                    ->prototype('scalar')->end()
                ->end()
            ->end()
        ->end();

        return $node;
    }

    /**
     * Configure underscored class namespace prefix naming strategy
     */
    private function getUnderscoredClassNamespacePrefixDefinition(): ArrayNodeDefinition
    {
        $node = new ArrayNodeDefinition('underscored_class_namespace_prefix');

        /**
         * @psalm-suppress all
         */
        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->enumNode('case')
                    ->info('Which case to use, lowercase or uppercase. Default is lowercase.')
                    ->values(array('lowercase', 'uppercase'))
                    ->defaultValue('lowercase')
                ->end()
                ->booleanNode('join_table_field_suffix')
                    ->defaultTrue()
                    ->info('Join table will get field name suffix enabling you to have multiple many-to-many relations without stating explicitly table names.')
                ->end()
                ->arrayNode('map')
                    ->requiresAtLeastOneElement()
                    ->info('Map of FQCNs prefixes and table prefixes to use for naming.')
                    ->useAttributeAsKey('namespace')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('whitelist')
                    ->beforeNormalization()
                        ->ifString()
                        ->then(static function ($value) {
                            return [$value];
                        })
                    ->end()
                    ->info('Define for which FQCNs prefixes table prefixes should be applied.')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('blacklist')
                    ->beforeNormalization()
                        ->ifString()
                        ->then(static function ($value) {
                            return [$value];
                        })
                    ->end()
                    ->info('Define for which FQCNs prefixes not to apply table prefixes.')
                    ->prototype('scalar')->end()
                ->end()
            ->end()
        ->end();

        return $node;
    }

    /**
     * Configure namer collection
     */
    private function getNamerCollectionDefinition(): ArrayNodeDefinition
    {
        $node = new ArrayNodeDefinition('underscored_namer_collection');

        /**
         * @psalm-suppress all
         */
        $node
            ->addDefaultsIfNotSet()
            ->fixXmlConfig('namer')
            ->children()
                ->scalarNode('default')
                    ->info('Default namer which will determine default name.')
                    ->defaultValue('doctrine.orm.naming_strategy.underscore')
                ->end()
                ->arrayNode('namers')
                    ->beforeNormalization()
                        ->ifString()
                        ->then(static function ($value) {
                            return [$value];
                        })
                    ->end()
                    ->requiresAtLeastOneElement()
                    ->info('Concurrent namers (referenced as service) which will propose different name, if applicable.')
                    ->prototype('scalar')->end()
                    ->defaultValue([
                        'runopencode.doctrine.orm.naming_strategy.underscored_class_namespace_prefix',
                        'runopencode.doctrine.orm.naming_strategy.underscored_bundle_prefix',
                    ])
                ->end()
                ->booleanNode('join_table_field_suffix')
                    ->defaultTrue()
                    ->info('Join table will get field name suffix enabling you to have multiple many-to-many relations without stating explicitly table names.')
                ->end()
            ->end()
        ->end();

        return $node;
    }
}
