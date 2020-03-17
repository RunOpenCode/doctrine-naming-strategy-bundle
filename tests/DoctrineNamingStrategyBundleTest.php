<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\DoctrineNamingStrategy\Tests;

use PHPUnit\Framework\TestCase;
use RunOpenCode\Bundle\DoctrineNamingStrategy\DependencyInjection\Extension;
use RunOpenCode\Bundle\DoctrineNamingStrategy\DoctrineNamingStrategyBundle;

class DoctrineNamingStrategyBundleTest extends TestCase
{
    /**
     * @test
     */
    public function itGetsContainerExtension(): void
    {
        $bundle = new DoctrineNamingStrategyBundle();
        $this->assertInstanceOf(Extension::class, $bundle->getContainerExtension());
    }
}
