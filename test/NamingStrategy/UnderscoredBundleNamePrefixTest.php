<?php
/*
 * This file is part of the Doctrine Naming Strategy Bundle, an RunOpenCode project.
 *
 * (c) 2015 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\Bundle\DoctrineNamingStrategy\Tests\NamingStrategy;

use Symfony\Component\HttpKernel\Kernel;
use RunOpenCode\Bundle\DoctrineNamingStrategy\NamingStrategy\UnderscoredBundleNamePrefix;
use RunOpenCode\Bundle\DoctrineNamingStrategy\DoctrineNamingStrategyBundle;

class UnderscoredBundleNamePrefixTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function classToTableNameLowercase()
    {
        $strategy = new UnderscoredBundleNamePrefix($this->mockKernel(), array(
            'map' => array(
                'DoctrineNamingStrategyBundle' => 'my_prefix'
            )
        ));

        $this->assertSame('my_prefix_some_entity', $strategy->classToTableName('RunOpenCode\\Bundle\\DoctrineNamingStrategy\\Entity\\SomeEntity'));
    }

    /**
     * @test
     */
    public function classToTableNameUppercase()
    {
        $strategy = new UnderscoredBundleNamePrefix($this->mockKernel(), array(
            'map' => array(
                'DoctrineNamingStrategyBundle' => 'my_prefix',
            ),
            'case' => CASE_UPPER
        ));

        $this->assertSame('MY_PREFIX_SOME_ENTITY', $strategy->classToTableName('RunOpenCode\\Bundle\\DoctrineNamingStrategy\\Entity\\SomeEntity'));
    }

    /**
     * @test
     */
    public function joinTableName()
    {
        $strategy = new UnderscoredBundleNamePrefix($this->mockKernel(), array(
            'map' => array(
                'DoctrineNamingStrategyBundle' => 'my_prefix',
            )
        ));

        $this->assertSame('my_prefix_some_entity_my_prefix_other_entity_field_name', $strategy->joinTableName(
            'RunOpenCode\\Bundle\\DoctrineNamingStrategy\\Entity\\SomeEntity',
            'RunOpenCode\\Bundle\\DoctrineNamingStrategy\\Entity\\OtherEntity',
            'fieldName'
        ));
    }

    /**
     * @test
     *
     * @expectedException \RuntimeException
     */
    public function invalidConfiguration()
    {
        new UnderscoredBundleNamePrefix($this->mockKernel(), array(
            'map' => array(
                'DoctrineNamingStrategyBundle' => 'my_prefix'
            ),
            'blacklist' => array(
                'DoctrineNamingStrategyBundle'
            ),
            'whitelist' => array(
                'DoctrineNamingStrategyBundle'
            )
        ));
    }

    /**
     * @test
     */
    public function blacklisted()
    {
        $strategy = new UnderscoredBundleNamePrefix($this->mockKernel(), array(
            'map' => array(
                'DoctrineNamingStrategyBundle' => 'my_prefix'
            ),
            'blacklist' => array(
                'DoctrineNamingStrategyBundle'
            )
        ));

        $this->assertSame('some_entity', $strategy->classToTableName('RunOpenCode\\Bundle\\DoctrineNamingStrategy\\Entity\\SomeEntity'));
    }

    /**
     * @test
     */
    public function whitelisted()
    {
        $strategy = new UnderscoredBundleNamePrefix($this->mockKernel(), array(
            'map' => array(
                'DoctrineNamingStrategyBundle' => 'my_prefix'
            ),
            'whitelist' => array(
                'SomeNonExistingBundle'
            )
        ));

        $this->assertSame('some_entity', $strategy->classToTableName('RunOpenCode\\Bundle\\DoctrineNamingStrategy\\Entity\\SomeEntity'));
    }

    private function mockKernel()
    {
        $stub = $this->getMockBuilder('Symfony\\Component\\HttpKernel\\Kernel')->disableOriginalConstructor()->getMock();

        $stub->method('getBundles')
            ->willReturn(array(
                new DoctrineNamingStrategyBundle()  // For now, it is not possible to mock with namespace, so we will use bundle's bundle class
            ));

        return $stub;
    }
}
