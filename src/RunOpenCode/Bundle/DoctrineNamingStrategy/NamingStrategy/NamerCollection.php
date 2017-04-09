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

/**
 * Class NamerCollection
 *
 * @package RunOpenCode\Bundle\DoctrineNamingStrategy\NamingStrategy
 */
class NamerCollection implements NamingStrategy
{
    /**
     * Concatenate different naming strategy outputs with underscore character.
     */
    const UNDERSCORE = 'underscore';

    /**
     * Concatenate different naming strategy outputs without any character.
     */
    const NOTHING = 'nothing';

    /**
     * Concatenate different naming strategy outputs with camel casing.
     */
    const UCFIRST = 'ucfirst';

    /**
     * @var NamingStrategy
     */
    protected $defaultNamingStrategy;

    /**
     * @var NamingStrategy[]
     */
    protected $concurrentNamingStrategies;

    /**
     * @var string
     */
    protected $concatenation;

    /**
     * @var bool
     */
    protected $joinTableFieldSuffix;

    public function __construct(NamingStrategy $defaultNamingStrategy, array $concurrentNamingStrategies = [], array $configuration = [])
    {
        $this->defaultNamingStrategy = $defaultNamingStrategy;

        $this->concurrentNamingStrategies = [];

        foreach ($concurrentNamingStrategies as $namingStrategy) {
            $this->registerNamingStrategy($namingStrategy);
        }

        $configuration = array_merge([
            'concatenation' => self::UNDERSCORE,
            'joinTableFieldSuffix' => true
        ], $configuration);

        $this->concatenation = $configuration['concatenation'];
        $this->joinTableFieldSuffix = $configuration['joinTableFieldSuffix'];
    }

    /**
     * Register naming strategy.
     *
     * @param NamingStrategy $namingStrategy
     *
     * @return NamerCollection $this
     */
    public function registerNamingStrategy(NamingStrategy $namingStrategy)
    {
        $this->concurrentNamingStrategies[] = $namingStrategy;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function classToTableName($className)
    {
        $defaultName = $this->defaultNamingStrategy->classToTableName($className);

        /**
         * @var NamingStrategy $concurrentNamer
         */
        foreach ($this->concurrentNamingStrategies as $concurrentNamer) {

            if (($newProposal = $concurrentNamer->classToTableName($className)) !== $defaultName) {
                return $newProposal;
            }
        }

        return $defaultName;
    }

    /**
     * {@inheritdoc}
     */
    public function propertyToColumnName($propertyName, $className = null)
    {
        $defaultName = $this->defaultNamingStrategy->propertyToColumnName($propertyName, $className);

        /**
         * @var NamingStrategy $concurrentNamer
         */
        foreach ($this->concurrentNamingStrategies as $concurrentNamer) {

            if (($newProposal = $concurrentNamer->propertyToColumnName($propertyName, $className)) !== $defaultName) {
                return $newProposal;
            }
        }

        return $defaultName;
    }

    /**
     * {@inheritdoc}
     */
    public function embeddedFieldToColumnName($propertyName, $embeddedColumnName, $className = null, $embeddedClassName = null)
    {
        $defaultName = $this->defaultNamingStrategy->embeddedFieldToColumnName($propertyName, $embeddedColumnName, $className, $embeddedClassName);

        /**
         * @var NamingStrategy $concurrentNamer
         */
        foreach ($this->concurrentNamingStrategies as $concurrentNamer) {

            if (($newProposal = $concurrentNamer->embeddedFieldToColumnName($propertyName, $embeddedColumnName, $className, $embeddedClassName)) != $defaultName) {
                return $newProposal;
            }
        }

        return $defaultName;
    }

    /**
     * {@inheritdoc}
     */
    public function referenceColumnName()
    {
        $defaultName = $this->defaultNamingStrategy->referenceColumnName();

        /**
         * @var NamingStrategy $concurrentNamer
         */
        foreach ($this->concurrentNamingStrategies as $concurrentNamer) {

            if (($newProposal = $concurrentNamer->referenceColumnName()) != $defaultName) {
                return $newProposal;
            }
        }

        return $defaultName;
    }

    /**
     * {@inheritdoc}
     */
    public function joinColumnName($propertyName/*, $className = null*/)
    {
        $defaultName = $this->defaultNamingStrategy->joinColumnName($propertyName/*, $className */);

        /**
         * @var NamingStrategy $concurrentNamer
         */
        foreach ($this->concurrentNamingStrategies as $concurrentNamer) {

            if (($newProposal = $concurrentNamer->joinColumnName($propertyName/*, $className */)) != $defaultName) {
                return $newProposal;
            }
        }

        return $defaultName;
    }

    /**
     * {@inheritdoc}
     */
    public function joinTableName($sourceEntity, $targetEntity, $propertyName = null)
    {
        switch ($this->concatenation) {
            case self::UCFIRST:
                return
                    $this->classToTableName($sourceEntity) . ucfirst($this->classToTableName($targetEntity))
                    .
                    (($this->joinTableFieldSuffix && !empty($propertyName)) ? ucfirst($this->propertyToColumnName($propertyName, $sourceEntity)) : '')
                    ;
                break;
            case self::NOTHING:
                return
                    $this->classToTableName($sourceEntity) . $this->classToTableName($targetEntity)
                    .
                    (($this->joinTableFieldSuffix && !empty($propertyName)) ? $this->propertyToColumnName($propertyName, $sourceEntity) : '')
                    ;
                break;
            case self::UNDERSCORE: // FALL TROUGH
            default:
                return
                    $this->classToTableName($sourceEntity) . '_' . $this->classToTableName($targetEntity)
                    .
                    (($this->joinTableFieldSuffix && !empty($propertyName)) ? '_' . $this->propertyToColumnName($propertyName, $sourceEntity) : '')
                    ;
                break;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function joinKeyColumnName($entityName, $referencedColumnName = null)
    {
        switch ($this->concatenation) {
            case self::UCFIRST:
                return $this->classToTableName($entityName) . ucfirst(($referencedColumnName ?: $this->referenceColumnName()));
                break;
            case self::NOTHING:
                return $this->classToTableName($entityName) . ($referencedColumnName ?: $this->referenceColumnName());
                break;
            case self::UNDERSCORE: // FALL TROUGH
            default:
                return $this->classToTableName($entityName) . '_' . ($referencedColumnName ?: $this->referenceColumnName());
                break;
        }
    }
}
