<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\DoctrineNamingStrategy\NamingStrategy;

use Doctrine\ORM\Mapping\NamingStrategy;
use RunOpenCode\Bundle\DoctrineNamingStrategy\Exception\RuntimeException;

/**
 * @psalm-suppress UnusedClass
 */
final class UnderscoredClassNamespacePrefix implements NamingStrategy
{
    private int $case;

    protected bool $joinTableFieldSuffix;

    /**
     * @var string[]
     */
    protected array $whitelist;

    /**
     * @var string[]
     */
    protected array $blacklist;

    /**
     * @var array<string, string>
     */
    protected array $map;

    /**
     * @psalm-param array{case?: int, map?: array<string, string>, blacklist?: string[], whitelist?: string[], join_table_field_suffix?: bool } $configuration
     *
     * @throws RuntimeException
     */
    public function __construct(array $configuration = [])
    {
        /**
         * @psalm-var array{case: int, map: array<string, string>, blacklist: string[], whitelist: string[], join_table_field_suffix: bool } $configuration
         */
        $configuration = array_merge([
            'case'                    => CASE_LOWER,
            'map'                     => [],
            'whitelist'               => [],
            'blacklist'               => [],
            'join_table_field_suffix' => true,
        ], $configuration);

        if (\count($configuration['whitelist']) > 0 && \count($configuration['blacklist']) > 0) {
            throw new RuntimeException('You can use whitelist or blacklist or none of mentioned lists, but not booth.');
        }

        $this->case = $configuration['case'];
        /**
         * @psalm-suppress MixedPropertyTypeCoercion
         */
        $this->map       = \array_map(\Closure::bind(function (string $prefix) {
            return $this->underscore($prefix);
        }, $this), $configuration['map']);
        $this->blacklist = \array_map(static function (string $class) {
            return \ltrim($class, '\\');
        }, $configuration['blacklist']);
        $this->whitelist = \array_map(static function (string $class) {
            return \ltrim($class, '\\');
        }, $configuration['whitelist']);

        $this->joinTableFieldSuffix = $configuration['join_table_field_suffix'];
    }

    /**
     * {@inheritdoc}
     */
    public function classToTableName($className): string
    {
        $prefix   = $this->getTableNamePrefix($className);
        $position = \strrpos($className, '\\');

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
        $suffix    = $this->joinTableFieldSuffix && null !== $propertyName ? '_' . $this->propertyToColumnName($propertyName, $sourceEntity) : '';

        return $tableName . $suffix;
    }

    /**
     * {@inheritdoc}
     */
    public function joinKeyColumnName($entityName, $referencedColumnName = null): string
    {
        return $this->classToTableName($entityName) . '_' .
            ($referencedColumnName ? $this->underscore($referencedColumnName) : $this->referenceColumnName());
    }

    private function getTableNamePrefix(string $className): string
    {
        $className = \ltrim($className, '\\');

        foreach ($this->blacklist as $blacklist) {

            if (0 === \strpos($className, $blacklist)) {
                return '';
            }
        }

        foreach ($this->map as $namespace => $prefix) {

            if (0 === \strpos($className, $namespace)) {

                foreach ($this->whitelist as $whitelistedNamespace) {

                    if (0 === \strpos($className, $whitelistedNamespace)) {
                        return $prefix . '_';
                    }
                }

                return 0 === \count($this->whitelist) ? ($prefix . '_') : '';
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
