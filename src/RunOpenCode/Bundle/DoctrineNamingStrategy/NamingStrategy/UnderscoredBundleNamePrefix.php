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
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class UnderscoredBundleNamePrefix
 *
 * @package RunOpenCode\Bundle\DoctrineNamingStrategy\NamingStrategy
 */
class UnderscoredBundleNamePrefix implements NamingStrategy
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
    protected $map;

    /**
     * UnderscoredBundleNamePrefix constructor.
     *
     * @param KernelInterface $kernel
     * @param array $options
     */
    public function __construct(KernelInterface $kernel, array $options = array())
    {
        $options = array_merge([
            'case' => CASE_LOWER,
            'map' => [],
            'whitelist' => [],
            'blacklist' => [],
            'join_table_field_suffix' => true,
        ], $options);

        if (count($options['whitelist']) > 0 && count($options['blacklist']) > 0) {
            throw new RuntimeException('You can use whitelist or blacklist or none of mentioned lists, but not booth.');
        }

        $this->case = $options['case'];
        $this->joinTableFieldSuffix = $options['join_table_field_suffix'];

        $this->map = $this->getNamingMap($kernel, $options);
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
        return $this->underscore($propertyName).'_'.$this->referenceColumnName();
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
     * Get bundle naming map.
     *
     * @param KernelInterface $kernel
     * @param array $configuration
     *
     * @return array
     */
    private function getNamingMap(KernelInterface $kernel, array $configuration)
    {
        $map = [];

        /**
         * @var BundleInterface $bundle;
         */
        foreach ($kernel->getBundles() as $bundle) {

            if (count($configuration['blacklist']) > 0 && in_array($bundle->getName(), $configuration['blacklist'])) {
                continue;
            }

            if (count($configuration['whitelist']) > 0 && !in_array($bundle->getName(), $configuration['whitelist'])) {
                continue;
            }

            $bundleNamespace = (new \ReflectionClass(get_class($bundle)))->getNamespaceName();
            $bundleName = $bundle->getName();

            if (isset($configuration['map'][$bundleName])) {
                $map[$this->underscore($configuration['map'][$bundleName])] = $bundleNamespace;
                continue;
            }

            $bundleName = preg_replace('/Bundle$/', '', $bundleName);

            if (isset($configuration['map'][$bundleName])) {
                $map[$this->underscore($configuration['map'][$bundleName])] = $bundleNamespace;
                continue;
            }

            $map[ $this->underscore($bundleName) ] = $bundleNamespace;
        }

        return $map;
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

        foreach ($this->map as $prefix => $namespace) {

            if (strpos($className, $namespace) === 0) {
                return $prefix.'_';
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
