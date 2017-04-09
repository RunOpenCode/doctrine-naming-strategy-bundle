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

use Doctrine\ORM\Mapping\UnderscoreNamingStrategy;

class UnderscoredClassNamespacePrefix extends UnderscoreNamingStrategy
{
    /**
     * @var array
     */
    protected $map;

    /**
     * @var array
     */
    protected $whitelist;

    /**
     * @var array
     */
    protected $blacklist;

    /**
     * @var bool
     */
    protected $joinTableFieldSuffix;

    public function __construct(array $configuration = array())
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

        $this->map = $configuration['map'];
        $this->blacklist = $configuration['blacklist'];
        $this->whitelist = $configuration['whitelist'];
        $this->joinTableFieldSuffix = $configuration['joinTableFieldSuffix'];

        parent::__construct($configuration['case']);

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
            (($this->joinTableFieldSuffix && !empty($propertyName)) ? '_' . $this->propertyToColumnName($propertyName, $sourceEntity) : '');
    }

    /**
     * Find prefix for table from map.
     *
     * @param string $className
     * @return string
     */
    private function getTableNamePrefix($className)
    {
        foreach ($this->blacklist as $blacklistedFqcn) {

            if (strpos($className, $blacklistedFqcn) === 0) {
                return '';
            }
        }

        foreach ($this->map as $fqcnPrefix => $tablePrefix) {

            if (strpos($className, $fqcnPrefix) === 0) {

                if (count($this->whitelist) > 0) {

                    foreach ($this->whitelist as $whitelistedFqcn) {

                        if (strpos($className, $whitelistedFqcn) === 0) {
                            return $this->propertyToColumnName($tablePrefix);
                        }
                    }

                    return '';

                } else {

                    return $this->propertyToColumnName($tablePrefix);
                }
            }
        }

        return '';
    }
}
