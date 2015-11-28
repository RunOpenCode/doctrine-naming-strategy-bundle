<?php

namespace RunOpenCode\Bundle\DoctrineNamingStrategy\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('run_open_code_doctrine_naming_strategy');

        $rootNode
            ->children()

                ->arrayNode('underscored_bundle_prefix')
                    ->children()
                        ->enumNode('case')
                            ->info('Which case to use, lowercase or uppercase. Default is lowercase.')
                            ->values(array('lowercase', 'uppercase'))
                            ->defaultValue('lowercase')
                        ->end()
                        ->arrayNode('map')
                            ->info('Map of short bundle names and prefixes, if you do not want to use full bundle name in prefix. Useful when bundle name is too long, considering that, per example, MySQL has 60 chars table name limit.')
                            ->useAttributeAsKey('name')
                            ->prototype('scalar')
                            ->end()
                        ->end()
                        ->arrayNode('whitelist')
                            ->info('Define for which bundles to apply prefixes.')
                            ->prototype('scalar')
                            ->end()
                        ->end()
                        ->arrayNode('blacklist')
                            ->info('Define for which bundles not to apply prefixes.')
                            ->prototype('scalar')
                            ->end()
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('underscored_class_namespace_prefix')
                    ->children()
                        ->enumNode('case')
                            ->info('Which case to use, lowercase or uppercase. Default is lowercase.')
                            ->values(array('lowercase', 'uppercase'))
                            ->defaultValue('lowercase')
                        ->end()
                        ->arrayNode('map')
                            ->requiresAtLeastOneElement()
                            ->info('Map of FQCNs prefixes and table prefixes to use for naming.')
                            ->useAttributeAsKey('name')
                            ->prototype('scalar')
                            ->end()
                        ->end()
                        ->arrayNode('whitelist')
                            ->info('Define for which FQCNs prefixes table prefixes should be applied.')
                            ->prototype('scalar')
                            ->end()
                        ->end()
                        ->arrayNode('blacklist')
                            ->info('Define for which FQCNs prefixes not to apply table prefixes.')
                            ->prototype('scalar')
                            ->end()
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('namer_collection')
                    ->children()
                        ->scalarNode('default')
                            ->info('Default namer which will determine default name.')
                            ->defaultValue('doctrine.orm.naming_strategy.underscore')
                        ->end()
                        ->arrayNode('namers')
                            ->requiresAtLeastOneElement()
                            ->info('Concurrent namers (referenced as service) which will propose different name, if applicable.')
                            ->prototype('scalar')
                            ->end()
                        ->end()
                    ->end()
                ->end()

            ->end()
        ->end();

        return $treeBuilder;
    }

}