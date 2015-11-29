<?php
/*
 * This file is part of the Doctrine Naming Strategy Bundle, an RunOpenCode project.
 *
 * (c) 2015 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\Bundle\DoctrineNamingStrategy\NamingStrategy;

use Doctrine\ORM\Mapping\NamingStrategy;

class NamerCollection implements NamingStrategy
{
    /**
     * @var NamingStrategy
     */
    protected $defaultNamer;

    /**
     * @var NamingStrategy[]
     */
    protected $concurrentNamers;

    public function __construct(NamingStrategy $defaultNamer, array $concurrentNamers)
    {
        $this->defaultNamer = $defaultNamer;
        $this->concurrentNamers = $concurrentNamers;
    }

    /**
     * {@inheritdoc}
     */
    public function classToTableName($className)
    {
        $defaultName = $this->defaultNamer->classToTableName($className);

        /**
         * @var NamingStrategy $concurrentNamer
         */
        foreach ($this->concurrentNamers as $concurrentNamer) {

            if (($newProposal = $concurrentNamer->classToTableName($className)) != $defaultName) {
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
        $defaultName = $this->defaultNamer->propertyToColumnName($propertyName, $className);

        /**
         * @var NamingStrategy $concurrentNamer
         */
        foreach ($this->concurrentNamers as $concurrentNamer) {

            if (($newProposal = $concurrentNamer->propertyToColumnName($propertyName, $className)) != $defaultName) {
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
        $defaultName = $this->defaultNamer->embeddedFieldToColumnName($propertyName, $embeddedColumnName, $className, $embeddedClassName);

        /**
         * @var NamingStrategy $concurrentNamer
         */
        foreach ($this->concurrentNamers as $concurrentNamer) {

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
        $defaultName = $this->defaultNamer->referenceColumnName();

        /**
         * @var NamingStrategy $concurrentNamer
         */
        foreach ($this->concurrentNamers as $concurrentNamer) {

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
        $defaultName = $this->defaultNamer->joinColumnName($propertyName/*, $className */);

        /**
         * @var NamingStrategy $concurrentNamer
         */
        foreach ($this->concurrentNamers as $concurrentNamer) {

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
        $defaultName = $this->defaultNamer->joinTableName($sourceEntity, $targetEntity, $propertyName);

        /**
         * @var NamingStrategy $concurrentNamer
         */
        foreach ($this->concurrentNamers as $concurrentNamer) {

            if (($newProposal = $concurrentNamer->joinTableName($sourceEntity, $targetEntity, $propertyName)) != $defaultName) {
                return $newProposal;
            }
        }

        return $defaultName;
    }

    /**
     * {@inheritdoc}
     */
    public function joinKeyColumnName($entityName, $referencedColumnName = null)
    {
        $defaultName = $this->defaultNamer->joinKeyColumnName($entityName, $referencedColumnName);

        /**
         * @var NamingStrategy $concurrentNamer
         */
        foreach ($this->concurrentNamers as $concurrentNamer) {

            if (($newProposal = $concurrentNamer->joinKeyColumnName($entityName, $referencedColumnName)) != $defaultName) {
                return $newProposal;
            }
        }

        return $defaultName;
    }
}