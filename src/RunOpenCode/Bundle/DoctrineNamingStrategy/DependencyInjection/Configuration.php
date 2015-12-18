<?php
/*
 * This file is part of the Doctrine Naming Strategy Bundle, an RunOpenCode project.
 *
 * (c) 2015 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\Bundle\DoctrineNamingStrategy\DependencyInjection;

use RunOpenCode\Bundle\DoctrineNamingStrategy\NamingStrategy\NamerCollection;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
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

        $rootNode = $treeBuilder->root('underscored_bundle_prefix');

        $rootNode
            ->children()
                ->append($this->getUnderscoredBundlePrefixDefinition())
                ->append($this->getUnderscoredClassNamespacePrefixDefinition())
                ->append($this->getNamerCollectionDefinition())
            ->end()
        ->end();

        return $treeBuilder;
    }

    protected function getUnderscoredBundlePrefixDefinition()
    {
        $node = new ArrayNodeDefinition('run_open_code_doctrine_naming_strategy');

        $node
            ->children()
                ->enumNode('case')
                    ->info('Which case to use, lowercase or uppercase. Default is lowercase.')
                    ->values(array('lowercase', 'uppercase'))
                    ->defaultValue('lowercase')
                ->end()
                ->booleanNode('joinTableFieldSuffix')
                    ->defaultTrue()
                    ->info('Join table will get field name suffix enabling you to have multiple many-to-many relations without stating explicitly table names.')
                ->end()
                ->arrayNode('map')
                    ->info('Map of short bundle names and prefixes, if you do not want to use full bundle name in prefix. Useful when bundle name is too long, considering that, per example, MySQL has 60 chars table name limit.')
                    ->useAttributeAsKey('name')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('whitelist')
                    ->info('Define for which bundles to apply prefixes.')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('blacklist')
                    ->info('Define for which bundles not to apply prefixes.')
                    ->prototype('scalar')->end()
                ->end()
            ->end()
        ->end();

        return $node;
    }

    protected function getUnderscoredClassNamespacePrefixDefinition()
    {
        $node = new ArrayNodeDefinition('underscored_class_namespace_prefix');

        $node
            ->children()
                ->enumNode('case')
                    ->info('Which case to use, lowercase or uppercase. Default is lowercase.')
                    ->values(array('lowercase', 'uppercase'))
                    ->defaultValue('lowercase')
                ->end()
                ->booleanNode('joinTableFieldSuffix')
                    ->defaultTrue()
                    ->info('Join table will get field name suffix enabling you to have multiple many-to-many relations without stating explicitly table names.')
                ->end()
                ->arrayNode('map')
                    ->requiresAtLeastOneElement()
                    ->info('Map of FQCNs prefixes and table prefixes to use for naming.')
                    ->useAttributeAsKey('name')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('whitelist')
                    ->info('Define for which FQCNs prefixes table prefixes should be applied.')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('blacklist')
                    ->info('Define for which FQCNs prefixes not to apply table prefixes.')
                    ->prototype('scalar')->end()
                ->end()
            ->end()
        ->end();

        return $node;
    }

    protected function getNamerCollectionDefinition()
    {
        $node = new ArrayNodeDefinition('namer_collection');

        $node
            ->children()
                ->scalarNode('default')
                    ->info('Default namer which will determine default name.')
                    ->defaultValue('doctrine.orm.naming_strategy.underscore')
                ->end()
                ->arrayNode('namers')
                    ->requiresAtLeastOneElement()
                    ->info('Concurrent namers (referenced as service) which will propose different name, if applicable.')
                    ->prototype('scalar')->end()
                ->end()
                ->enumNode('concatenation')
                    ->info('How to concatenate join table names and join key column names considering that different naming strategies can be included in mix.')
                    ->values(array(NamerCollection::UNDERSCORE, NamerCollection::NOTHING, NamerCollection::UCFIRST))
                    ->defaultValue(NamerCollection::UNDERSCORE)
                ->end()
                ->booleanNode('joinTableFieldSuffix')
                    ->defaultTrue()
                    ->info('Join table will get field name suffix enabling you to have multiple many-to-many relations without stating explicitly table names.')
                ->end()
            ->end()
        ->end();

        return $node;
    }

}
