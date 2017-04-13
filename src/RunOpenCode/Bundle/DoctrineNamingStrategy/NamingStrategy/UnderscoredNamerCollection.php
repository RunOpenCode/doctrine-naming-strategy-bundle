<?php
/*
 * This file is part of the Doctrine Naming Strategy Bundle, an RunOpenCode project.
 *
 * (c) 2017 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\Bundle\DoctrineNamingStrategy\NamingStrategy;

use Doctrine\ORM\Mapping\NamingStrategy;
use RunOpenCode\Bundle\DoctrineNamingStrategy\Exception\InvalidArgumentException;

/**
 * Class UnderscoredNamerCollection
 *
 * @package RunOpenCode\Bundle\DoctrineNamingStrategy\NamingStrategy
 */
class UnderscoredNamerCollection implements NamingStrategy
{
    /**
     * @var NamingStrategy
     */
    protected $defaultNamingStrategy;

    /**
     * @var NamingStrategy[]
     */
    protected $concurrentNamingStrategies;

    /**
     * @var bool
     */
    protected $joinTableFieldSuffix;

    /**
     * UnderscoredNamerCollection constructor.
     *
     * @param NamingStrategy $defaultNamingStrategy
     * @param NamingStrategy[] $concurrentNamingStrategies
     * @param array $configuration
     */
    public function __construct(NamingStrategy $defaultNamingStrategy, array $concurrentNamingStrategies = [], array $configuration = [])
    {
        $this->defaultNamingStrategy = $defaultNamingStrategy;

        $this->concurrentNamingStrategies = [];

        foreach ($concurrentNamingStrategies as $namingStrategy) {
            $this->registerNamingStrategy($namingStrategy);
        }

        $configuration = array_merge([
            'join_table_field_suffix' => true,
        ], $configuration);

        $this->joinTableFieldSuffix = $configuration['join_table_field_suffix'];
    }

    /**
     * Register naming strategy.
     *
     * @param NamingStrategy $namingStrategy
     *
     * @return UnderscoredNamerCollection $this
     *
     * @throws \RunOpenCode\Bundle\DoctrineNamingStrategy\Exception\InvalidArgumentException
     */
    public function registerNamingStrategy(NamingStrategy $namingStrategy)
    {
        if ($namingStrategy === $this->defaultNamingStrategy) {
            throw new InvalidArgumentException('Concurent naming strategy can not be default naming strategy.');
        }

        $this->concurrentNamingStrategies[] = $namingStrategy;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function classToTableName($className)
    {
        return $this->findNamer($className)->classToTableName($className);
    }

    /**
     * {@inheritdoc}
     */
    public function propertyToColumnName($propertyName, $className = null)
    {
        return $this->findNamer($className)->propertyToColumnName($propertyName, $className);
    }

    /**
     * {@inheritdoc}
     */
    public function embeddedFieldToColumnName($propertyName, $embeddedColumnName, $className = null, $embeddedClassName = null)
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
    public function referenceColumnName()
    {
        return $this->defaultNamingStrategy->referenceColumnName();
    }

    /**
     * {@inheritdoc}
     */
    public function joinColumnName($propertyName, $className = null)
    {
        return $this->findNamer($className)->joinColumnName($propertyName, $className);
    }

    /**
     * {@inheritdoc}
     */
    public function joinTableName($sourceEntity, $targetEntity, $propertyName = null)
    {
        return
            $this->classToTableName($sourceEntity).'_'.$this->classToTableName($targetEntity)
            .
            (($this->joinTableFieldSuffix && !empty($propertyName)) ? '_'.$this->propertyToColumnName($propertyName, $sourceEntity) : '')
            ;
    }

    /**
     * {@inheritdoc}
     */
    public function joinKeyColumnName($entityName, $referencedColumnName = null)
    {
        $namer = $this->findNamer($entityName);

        return $namer->classToTableName($entityName).'_'.($namer->propertyToColumnName($referencedColumnName) ?: $namer->referenceColumnName());
    }


    /**
     * Find applicable naming strategy for given class.
     *
     * @param string $className
     *
     * @return NamingStrategy
     */
    private function findNamer($className)
    {
        if ($className === null) {
            return $this->defaultNamingStrategy;
        }

        $defaultName = strtolower($this->defaultNamingStrategy->classToTableName($className));

        foreach ($this->concurrentNamingStrategies as $concurrentNamer) {

            if (strtolower($concurrentNamer->classToTableName($className)) !== $defaultName) {
                return $concurrentNamer;
            }
        }

        return $this->defaultNamingStrategy;
    }
}
