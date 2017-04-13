<?php
/*
 * This file is part of the Doctrine Naming Strategy Bundle, an RunOpenCode project.
 *
 * (c) 2017 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\Bundle\DoctrineNamingStrategy\Tests;

use PHPUnit\Framework\TestCase;
use RunOpenCode\Bundle\DoctrineNamingStrategy\DependencyInjection\Extension;
use RunOpenCode\Bundle\DoctrineNamingStrategy\DoctrineNamingStrategyBundle;

class DoctrineNamingStrategyBundleTest extends TestCase
{
    /**
     * @test
     */
    public function itGetsContainerExtension()
    {
        $bundle = new DoctrineNamingStrategyBundle();
        $this->assertInstanceOf(Extension::class, $bundle->getContainerExtension());
    }
}
