<?php

namespace Wexample\SymfonyDesignSystem;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Wexample\SymfonyDesignSystem\DependencyInjection\Compiler\DesignSystemTemplatesCompilerPass;
use Wexample\SymfonyDesignSystem\Interface\DesignSystemBundleInterface;
use Wexample\SymfonyHelpers\AbstractBundle;

class WexampleSymfonyDesignSystemBundle extends AbstractBundle implements DesignSystemBundleInterface
{
    public static function getDesignSystemFrontPaths(): array
    {
        return [
            __DIR__.'/../front/',
        ];
    }

    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(
            new DesignSystemTemplatesCompilerPass()
        );
    }
}
