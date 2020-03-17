<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\DoctrineNamingStrategy;

use RunOpenCode\Bundle\DoctrineNamingStrategy\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @psalm-suppress UnusedClass
 */
final class DoctrineNamingStrategyBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getContainerExtension(): ExtensionInterface
    {
        return new Extension();
    }
}
