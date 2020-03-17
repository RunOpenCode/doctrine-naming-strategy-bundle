<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\DoctrineNamingStrategy\NamingStrategy;

use Doctrine\ORM\Mapping\NamingStrategy;
use RunOpenCode\Bundle\DoctrineNamingStrategy\Exception\InvalidArgumentException;

/**
 * @psalm-suppress UnusedClass
 */
final class UnderscoredNamerCollection implements NamingStrategy
{
    protected NamingStrategy $defaultNamingStrategy;

    /**
     * @var NamingStrategy[]
     */
    protected array $concurrentNamingStrategies;

    protected bool $joinTableFieldSuffix;

    /**
     * @param NamingStrategy   $defaultNamingStrategy
     * @param NamingStrategy[] $concurrentNamingStrategies
     * @param array<array-key,mixed> $configuration
     */
    public function __construct(NamingStrategy $defaultNamingStrategy, array $concurrentNamingStrategies = [], array $configuration = [])
    {
        $this->defaultNamingStrategy      = $defaultNamingStrategy;
        $this->concurrentNamingStrategies = [];

        foreach ($concurrentNamingStrategies as $namingStrategy) {
            $this->registerNamingStrategy($namingStrategy);
        }

        $configuration = \array_merge([
            'join_table_field_suffix' => true,
        ], $configuration);

        $this->joinTableFieldSuffix = (bool)$configuration['join_table_field_suffix'];
    }

    /**
     * @throws InvalidArgumentException
     */
    public function registerNamingStrategy(NamingStrategy $namingStrategy): void
    {
        if ($namingStrategy === $this->defaultNamingStrategy) {
            throw new InvalidArgumentException('Concurent naming strategy can not be default naming strategy.');
        }

        $this->concurrentNamingStrategies[] = $namingStrategy;
    }

    /**
     * {@inheritdoc}
     */
    public function classToTableName($className): string
    {
        return $this->findNamer($className)->classToTableName($className);
    }

    /**
     * {@inheritdoc}
     */
    public function propertyToColumnName($propertyName, $className = null): string
    {
        return $this->findNamer($className)->propertyToColumnName($propertyName, $className);
    }

    /**
     * {@inheritdoc}
     */
    public function embeddedFieldToColumnName($propertyName, $embeddedColumnName, $className = null, $embeddedClassName = null): string
    {
        $namer = $this->findNamer($embeddedClassName);

        if ($namer === $this->defaultNamingStrategy && null !== $className) {
            $namer = $this->findNamer($className);
        }

        return $namer->embeddedFieldToColumnName($propertyName, $embeddedColumnName, $className, $embeddedClassName);
    }

    /**
     * {@inheritdoc}
     */
    public function referenceColumnName(): string
    {
        return $this->defaultNamingStrategy->referenceColumnName();
    }

    /**
     * {@inheritdoc}
     */
    public function joinColumnName($propertyName): string
    {
        return $this->findNamer()->joinColumnName($propertyName);
    }

    /**
     * {@inheritdoc}
     */
    public function joinTableName($sourceEntity, $targetEntity, $propertyName = null): string
    {
        return
            $this->classToTableName($sourceEntity) . '_' . $this->classToTableName($targetEntity)
            .
            (($this->joinTableFieldSuffix && !empty($propertyName)) ? '_' . $this->propertyToColumnName($propertyName, $sourceEntity) : '');
    }

    /**
     * {@inheritdoc}
     */
    public function joinKeyColumnName($entityName, $referencedColumnName = null): string
    {
        $namer              = $this->findNamer($entityName);
        $classTableName     = $namer->classToTableName($entityName);
        $propertyColumnName = $namer->referenceColumnName();

        if (null !== $referencedColumnName) {
            $propertyColumnName = $namer->propertyToColumnName($referencedColumnName) ?: $namer->referenceColumnName();
        }

        return \sprintf('%s_%s', $classTableName, $propertyColumnName);
    }

    private function findNamer(?string $className = null): NamingStrategy
    {
        if ($className === null) {
            return $this->defaultNamingStrategy;
        }

        $defaultName = \strtolower($this->defaultNamingStrategy->classToTableName($className));

        foreach ($this->concurrentNamingStrategies as $concurrentNamer) {

            if (\strtolower($concurrentNamer->classToTableName($className)) !== $defaultName) {
                return $concurrentNamer;
            }
        }

        return $this->defaultNamingStrategy;
    }
}
