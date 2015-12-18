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

use Doctrine\ORM\Mapping\UnderscoreNamingStrategy;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class UnderscoredBundleNamePrefix extends UnderscoreNamingStrategy
{
    /**
     * @var array
     */
    protected $map;

    /**
     * @var bool
     */
    protected $joinTableFieldSuffix;

    public function __construct(KernelInterface $kernel, array $configuration = array())
    {
        $configuration = array_merge(array(
            'case' => CASE_LOWER,
            'map' => array(),
            'whitelist' => array(),
            'blacklist' => array(),
            'joinTableFieldSuffix' => true
        ), $configuration);

        if (count($configuration['whitelist']) > 0 && count($configuration['blacklist']) > 0) {
            throw new \RuntimeException('You can use whitelist or blacklist or none of mentioned lists, but not booth.');
        }

        parent::__construct($configuration['case']);
        $this->buildMap($kernel, $configuration);

        $this->joinTableFieldSuffix = $configuration['joinTableFieldSuffix'];
    }

    /**
     * {@inheritdoc}
     */
    public function classToTableName($className)
    {
        return (($prefix = $this->getTableNamePrefix($className)) ? $prefix . '_' : '') . parent::classToTableName($className);
    }

    /**
     * {@inheritdoc}
     */
    public function joinTableName($sourceEntity, $targetEntity, $propertyName = null)
    {
        return
            parent::joinTableName($sourceEntity, $targetEntity, $propertyName)
            .
            (($this->joinTableFieldSuffix && $propertyName) ? '_' . $this->propertyToColumnName($propertyName, $sourceEntity) : '');
    }

    /**
     * Build bundle naming map.
     *
     * @param KernelInterface $kernel
     * @param array $configuration
     */
    private function buildMap(KernelInterface $kernel, array $configuration)
    {
        $this->map = array();
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

            $bundleClass = new \ReflectionClass(get_class($bundle));

            if (isset($configuration['map'][$bundle->getName()])) {
                $this->map[$this->propertyToColumnName($configuration['map'][$bundle->getName()])] = $bundleClass->getNamespaceName();
            } elseif (isset($configuration['map'][str_replace('Bundle', '', $bundle->getName())])) {
                $this->map[$this->propertyToColumnName($configuration['map'][str_replace('Bundle', '', $bundle->getName())])] = $bundleClass->getNamespaceName();
            } else {
                $this->map[str_replace('_bundle', '', $this->propertyToColumnName($bundle->getName()))] = $bundleClass->getNamespaceName();
            }
        }
    }

    /**
     * Find prefix for table from map.
     *
     * @param string $className
     * @return string
     */
    private function getTableNamePrefix($className)
    {
        foreach ($this->map as $prefix => $namespace) {

            if (strpos($className, $namespace) === 0) {
                return $prefix;
            }
        }

        return '';
    }
}
