<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\DoctrineNamingStrategy\NamingStrategy;

use Doctrine\ORM\Mapping\NamingStrategy;
use RunOpenCode\Bundle\DoctrineNamingStrategy\Exception\RuntimeException;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @psalm-suppress UnusedClass
 */
final class UnderscoredBundleNamePrefix implements NamingStrategy
{
    private int $case;

    private bool $joinTableFieldSuffix;

    /** @var array<string, string> */
    private array $map;

    /**
     * @psalm-param array{case?: int, map?: array<string, string>, blacklist?: string[], whitelist?: string[], join_table_field_suffix?: bool } $options
     *
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    public function __construct(KernelInterface $kernel, array $options = [])
    {
        /**
         * @psalm-var array{case: int, map: array<string, string>, blacklist: string[], whitelist: string[], join_table_field_suffix: bool } $options
         */
        $options = \array_merge([
            'case'                    => CASE_LOWER,
            'map'                     => [],
            'whitelist'               => [],
            'blacklist'               => [],
            'join_table_field_suffix' => true,
        ], $options);

        if (\count($options['whitelist']) > 0 && \count($options['blacklist']) > 0) {
            throw new RuntimeException('You can use whitelist or blacklist or none of mentioned lists, but not booth.');
        }

        $this->case                 = $options['case'];
        $this->joinTableFieldSuffix = $options['join_table_field_suffix'];
        $this->map                  = $this->getNamingMap($kernel, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function classToTableName($className): string
    {
        $prefix   = $this->getTableNamePrefix($className);
        $position = \strpos($className, '\\');

        if (false !== $position) {
            $className = \substr($className, ($position + 1));
        }

        return $prefix . $this->underscore($className);
    }

    /**
     * {@inheritdoc}
     */
    public function propertyToColumnName($propertyName, $className = null): string
    {
        return $this->underscore($propertyName);
    }

    /**
     * {@inheritdoc}
     */
    public function embeddedFieldToColumnName($propertyName, $embeddedColumnName, $className = null, $embeddedClassName = null): string
    {
        return $this->underscore($propertyName) . '_' . $this->underscore($embeddedColumnName);
    }

    /**
     * {@inheritdoc}
     */
    public function referenceColumnName(): string
    {
        return $this->case === CASE_UPPER ? 'ID' : 'id';
    }

    /**
     * {@inheritdoc}
     */
    public function joinColumnName($propertyName): string
    {
        return $this->underscore($propertyName) . '_' . $this->referenceColumnName();
    }

    /**
     * {@inheritdoc}
     */
    public function joinTableName($sourceEntity, $targetEntity, $propertyName = null): string
    {
        $tableName = $this->classToTableName($sourceEntity) . '_' . $this->classToTableName($targetEntity);

        return
            $tableName
            .
            (($this->joinTableFieldSuffix && null !== $propertyName) ? '_' . $this->propertyToColumnName($propertyName, $sourceEntity) : '');
    }

    /**
     * {@inheritdoc}
     */
    public function joinKeyColumnName($entityName, $referencedColumnName = null): string
    {
        return $this->classToTableName($entityName) . '_' .
            ($referencedColumnName ? $this->underscore($referencedColumnName) : $this->referenceColumnName());
    }

    /**
     * @psalm-param array{map: array<string, string>, blacklist: string[], whitelist: string[] } $configuration
     *
     * @return array<string, string>
     *
     * @throws \ReflectionException
     */
    private function getNamingMap(KernelInterface $kernel, array $configuration): array
    {
        $map = [];

        foreach ($kernel->getBundles() as $bundle) {

            $bundleName = $bundle->getName();

            if (\count($configuration['blacklist']) > 0 && \in_array($bundleName, $configuration['blacklist'], true)) {
                continue;
            }

            if (\count($configuration['whitelist']) > 0 && !\in_array($bundleName, $configuration['whitelist'], true)) {
                continue;
            }

            $bundleNamespace = (new \ReflectionClass(\get_class($bundle)))->getNamespaceName();

            if (isset($configuration['map'][$bundleName])) {
                $map[$this->underscore($configuration['map'][$bundleName])] = $bundleNamespace;
                continue;
            }

            /** @var string $bundleName */
            $bundleName = \preg_replace('/Bundle$/', '', $bundleName);

            if (isset($configuration['map'][$bundleName])) {
                $map[$this->underscore($configuration['map'][$bundleName])] = $bundleNamespace;
                continue;
            }

            $map[$this->underscore($bundleName)] = $bundleNamespace;
        }

        return $map;
    }

    private function getTableNamePrefix(string $className): string
    {
        $className = \ltrim($className, '\\');

        foreach ($this->map as $prefix => $namespace) {

            if (0 === \strpos($className, $namespace)) {
                return $prefix . '_';
            }
        }

        return '';
    }

    private function underscore(string $literal): string
    {
        /** @var string $literal */
        $literal = \preg_replace('/(?<=[a-z])([A-Z])/', '_$1', $literal);

        if (CASE_UPPER === $this->case) {
            return \strtoupper($literal);
        }

        return \strtolower($literal);
    }
}
