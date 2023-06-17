<?php

namespace Wexample\SymfonyDesignSystem;

use Symfony\Component\DependencyInjection\ContainerBuilder;

class WexampleSymfonyDesignSystemBundle extends AbstractDesignSystemBundle
{
    public function build(ContainerBuilder $container): void
    {
        $this->addFrontPathCompilerPass(
            $container,
            __DIR__.'/../front',
        );
    }
}
