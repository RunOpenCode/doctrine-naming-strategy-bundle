<?php

namespace RunOpenCode\Bundle\DoctrineNamingStrategy;

use RunOpenCode\Bundle\DoctrineNamingStrategy\DependencyInjection\Extension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DoctrineNamingStrategyBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        return new Extension();
    }
}