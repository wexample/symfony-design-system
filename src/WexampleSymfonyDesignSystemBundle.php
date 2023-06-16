<?php

namespace Wexample\SymfonyDesignSystem;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Wexample\SymfonyDesignSystem\DependencyInjection\Compiler\DesignSystemTemplatesCompilerPass;

class WexampleSymfonyDesignSystemBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(
            new DesignSystemTemplatesCompilerPass(
                __DIR__.'/../../../front',
                'SymfonyDesignSystemBundle'
            )
        );
    }
}
