<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\DoctrineNamingStrategy\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension as BaseExtension;
use Symfony\Component\Config\FileLocator;

final class Extension extends BaseExtension
{
    /**
     * {@inheritdoc}
     */
    public function getAlias(): string
    {
        return 'runopencode_doctrine_naming_strategy';
    }

    /**
     * {@inheritdoc}
     */
    public function getNamespace(): string
    {
        return 'http://www.runopencode.com/xsd-schema/doctrine-naming-strategy-bundle';
    }

    /**
     * {@inheritdoc}
     */
    public function getXsdValidationBasePath(): string
    {
        return __DIR__ . '/../Resources/config/schema';
    }

    /**
     * @param array<array-key, mixed> $config
     *
     * @throws \Exception
     */
    public function load(array $config, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config        = $this->processConfiguration($configuration, $config);
        $loader        = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $loader->load('services.xml');

        $this->configureUnderscoredBundlePrefixNamer($container, $config);
        $this->configureUnderscoredClassNamespacePrefixNamer($container, $config);
        $this->configureNamerCollection($container, $config);
    }

    /**
     * Configure 'runopencode.doctrine.orm.naming_strategy.underscored_bundle_prefix' naming strategy.
     *
     * @param array<array-key, mixed> $config
     */
    private function configureUnderscoredBundlePrefixNamer(ContainerBuilder $container, array $config): void
    {
        if (!isset($config['underscored_bundle_prefix'])) {
            return;
        }

        if (!$container->hasDefinition('runopencode.doctrine.orm.naming_strategy.underscored_bundle_prefix')) {
            return;
        }

        /** @psalm-suppress MissingThrowsDocblock */
        $definition                                  = $container->getDefinition('runopencode.doctrine.orm.naming_strategy.underscored_bundle_prefix');
        $config['underscored_bundle_prefix']['case'] = ('uppercase' === $config['underscored_bundle_prefix']['case']) ? CASE_UPPER : CASE_LOWER;
        $args                                        = $definition->getArguments();
        $args[1]                                     = $config['underscored_bundle_prefix'];

        $definition->setArguments($args);
    }

    /**
     * Configure 'runopencode.doctrine.orm.naming_strategy.underscored_class_namespace_prefix' naming strategy.
     *
     * @param array<array-key, mixed> $config
     */
    private function configureUnderscoredClassNamespacePrefixNamer(ContainerBuilder $container, array $config): void
    {
        if (!isset($config['underscored_class_namespace_prefix'])) {
            return;
        }

        if (!$container->hasDefinition('runopencode.doctrine.orm.naming_strategy.underscored_class_namespace_prefix')) {
            return;
        }

        /** @psalm-suppress MissingThrowsDocblock */
        $definition                                           = $container->getDefinition('runopencode.doctrine.orm.naming_strategy.underscored_class_namespace_prefix');
        $config['underscored_class_namespace_prefix']['case'] = ('uppercase' === $config['underscored_class_namespace_prefix']['case']) ? CASE_UPPER : CASE_LOWER;
        $args                                                 = $definition->getArguments();
        $args[0]                                              = $config['underscored_class_namespace_prefix'];

        $definition->setArguments($args);
    }

    /**
     * Configure 'runopencode.doctrine.orm.naming_strategy.underscored_namer_collection' naming strategy.
     *
     * @param array<array-key, mixed> $config
     */
    private function configureNamerCollection(ContainerBuilder $container, array $config): void
    {
        if (!isset($config['underscored_namer_collection'])) {
            return;
        }

        if (!$container->hasDefinition('runopencode.doctrine.orm.naming_strategy.underscored_namer_collection')) {
            return;
        }

        /** @psalm-suppress MissingThrowsDocblock */
        $definition = $container->getDefinition('runopencode.doctrine.orm.naming_strategy.underscored_namer_collection');

        $definition->setArguments([
            new Reference($config['underscored_namer_collection']['default']),
            array_map(static function (string $namerId) {
                return new Reference($namerId);
            }, $config['underscored_namer_collection']['namers']),
            [
                'join_table_field_suffix' => $config['underscored_namer_collection']['join_table_field_suffix'],
            ],
        ]);
    }
}
