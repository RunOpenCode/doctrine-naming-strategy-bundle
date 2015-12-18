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

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension as BaseExtension;
use Symfony\Component\Config\FileLocator;

class Extension extends BaseExtension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $config);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $this->configureUnderscoredBundlePrefixNamer($container, $config);
        $this->configureUnderscoredClassNamespacePrefixNamer($container, $config);
        $this->configureNamerCollection($container, $config);
    }

    /**
     * Configure 'run_open_code.doctrine.orm.naming_strategy.underscored_bundle_prefix' naming strategy.
     *
     * @param ContainerBuilder $container
     * @param array $config
     * @return Extension $this
     */
    private function configureUnderscoredBundlePrefixNamer(ContainerBuilder $container, array $config)
    {
        if (
            $container->hasDefinition('run_open_code.doctrine.orm.naming_strategy.underscored_bundle_prefix')
            &&
            isset($config['underscored_bundle_prefix'])
        ) {
            $definition = $container->getDefinition('run_open_code.doctrine.orm.naming_strategy.underscored_bundle_prefix');

            if ($config['underscored_bundle_prefix']['case'] == 'uppercase') {
                $config['underscored_bundle_prefix']['case'] = CASE_UPPER;
            } else {
                $config['underscored_bundle_prefix']['case'] = CASE_LOWER;
            }

            $args = $definition->getArguments();
            $args[1] = $config['underscored_bundle_prefix'];

            $definition->setArguments($args);
        }

        return $this;
    }

    /**
     * Configure 'run_open_code.doctrine.orm.naming_strategy.underscored_class_namespace_prefix' naming strategy.
     *
     * @param ContainerBuilder $container
     * @param array $config
     * @return Extension $this
     */
    private function configureUnderscoredClassNamespacePrefixNamer(ContainerBuilder $container, array $config)
    {
        if (
            $container->hasDefinition('run_open_code.doctrine.orm.naming_strategy.underscored_class_namespace_prefix')
            &&
            isset($config['underscored_class_namespace_prefix'])
        ) {
            $definition = $container->getDefinition('run_open_code.doctrine.orm.naming_strategy.underscored_class_namespace_prefix');

            if ($config['underscored_bundle_prefix']['case'] == 'uppercase') {
                $config['underscored_bundle_prefix']['case'] = CASE_UPPER;
            } else {
                $config['underscored_bundle_prefix']['case'] = CASE_LOWER;
            }

            $args = $definition->getArguments();
            $args[0] = $config['underscored_class_namespace_prefix'];

            $definition->setArguments($args);
        }

        return $this;
    }

    /**
     * Configure 'run_open_code.doctrine.orm.naming_strategy.namer_collection' naming strategy.
     *
     * @param ContainerBuilder $container
     * @param array $config
     * @return Extension $this
     */
    private function configureNamerCollection(ContainerBuilder $container, array $config)
    {
        if (
            $container->hasDefinition('run_open_code.doctrine.orm.naming_strategy.namer_collection')
            &&
            isset($config['namer_collection'])
        ) {
            $definition = $container->getDefinition('run_open_code.doctrine.orm.naming_strategy.namer_collection');

            $definition->setArguments(array(
                new Reference($config['namer_collection']['default']),
                array_map(function($namerId) {
                    return new Reference($namerId);
                }, $config['namer_collection']['namers'], array(
                    'concatenation' => $config['namer_collection']['concatenation'],
                    'joinTableFieldSuffix' => $config['namer_collection']['joinTableFieldSuffix']
                ))
            ));
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return "run_open_code_doctrine_naming_strategy";
    }
}
