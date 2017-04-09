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
use RunOpenCode\Bundle\DoctrineNamingStrategy\Exception\RuntimeException;

/**
 * Class UnderscoredClassNamespacePrefix
 *
 * @package RunOpenCode\Bundle\DoctrineNamingStrategy\NamingStrategy
 */
class UnderscoredClassNamespacePrefix implements NamingStrategy
{
    /**
     * @var int
     */
    protected $case = CASE_LOWER;

    /**
     * @var bool
     */
    protected $joinTableFieldSuffix;

    /**
     * @var array
     */
    protected $whitelist;

    /**
     * @var array
     */
    protected $blacklist;

    /**
     * @var array
     */
    protected $map;

    public function __construct(array $configuration = array())
    {
        $configuration = array_merge([
            'case' => CASE_LOWER,
            'map' => [],
            'whitelist' => [],
            'blacklist' => [],
            'joinTableFieldSuffix' => true
        ], $configuration);

        if (count($configuration['whitelist']) > 0 && count($configuration['blacklist']) > 0) {
            throw new RuntimeException('You can use whitelist or blacklist or none of mentioned lists, but not booth.');
        }

        $this->case = $configuration['case'];
        $this->map = array_map(\Closure::bind(function ($prefix) {
            return $this->underscore($prefix);
        }, $this), $configuration['map']);
        $this->blacklist = array_map(function($fqcn) {
            return ltrim($fqcn, '\\');
        }, $configuration['blacklist']);
        $this->whitelist = array_map(function($fqcn) {
            return ltrim($fqcn, '\\');
        }, $configuration['whitelist']);
        $this->joinTableFieldSuffix = $configuration['joinTableFieldSuffix'];
    }

    /**
     * {@inheritdoc}
     */
    public function classToTableName($className)
    {
        $prefix = $this->getTableNamePrefix($className);

        if (strpos($className, '\\') !== false) {
            $className = substr($className, strrpos($className, '\\') + 1);
        }

        return $prefix.$this->underscore($className);
    }

    /**
     * {@inheritdoc}
     */
    public function propertyToColumnName($propertyName, $className = null)
    {
        return $this->underscore($propertyName);
    }

    /**
     * {@inheritdoc}
     */
    public function embeddedFieldToColumnName($propertyName, $embeddedColumnName, $className = null, $embeddedClassName = null)
    {
        return $this->underscore($propertyName).'_'.$this->underscore($embeddedColumnName);
    }

    /**
     * {@inheritdoc}
     */
    public function referenceColumnName()
    {
        return $this->case === CASE_UPPER ?  'ID' : 'id';
    }

    /**
     * {@inheritdoc}
     */
    public function joinColumnName($propertyName, $className = null)
    {
        return $this->underscore($propertyName) . '_' . $this->referenceColumnName();
    }

    /**
     * {@inheritdoc}
     */
    public function joinTableName($sourceEntity, $targetEntity, $propertyName = null)
    {
        $tableName = $this->classToTableName($sourceEntity).'_'.$this->classToTableName($targetEntity);

        return
            $tableName
            .
            (($this->joinTableFieldSuffix && null !== $propertyName) ? '_'.$this->propertyToColumnName($propertyName, $sourceEntity) : '');
    }

    /**
     * {@inheritdoc}
     */
    public function joinKeyColumnName($entityName, $referencedColumnName = null)
    {
        return $this->classToTableName($entityName).'_'.
            ($referencedColumnName ? $this->underscore($referencedColumnName) : $this->referenceColumnName());
    }

    /**
     * Get prefix for table from map.
     *
     * @param string $className
     * @return string
     */
    protected function getTableNamePrefix($className)
    {
        $className = ltrim($className, '\\');

        foreach ($this->blacklist as $blacklist) {

            if (strpos($className, $blacklist) === 0) {
                return '';
            }
        }

        foreach ($this->map as $namespace => $prefix) {

            if (strpos($className, $namespace) === 0) {

                foreach ($this->whitelist as $whitelistedNamespace) {

                    if (strpos($className, $whitelistedNamespace) === 0) {
                        return $prefix.'_';
                    }
                }

                return 0 === count($this->whitelist) ? $prefix.'_' : '';
            }
        }

        return '';
    }

    /**
     * Build underscore version of given string.
     *
     * @param string $string
     * @return string
     */
    protected function underscore($string)
    {
        $string = preg_replace('/(?<=[a-z])([A-Z])/', '_$1', $string);

        if (CASE_UPPER === $this->case) {
            return strtoupper($string);
        }

        return strtolower($string);
    }
}
