<?php

namespace Wexample\SymfonyDesignSystem;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Wexample\SymfonyDesignSystem\DependencyInjection\Compiler\DesignSystemTemplatesCompilerPass;
use Wexample\SymfonyDesignSystem\Helper\DesignSystemHelper;
use Wexample\SymfonyHelpers\AbstractBundle;

abstract class AbstractDesignSystemBundle extends AbstractBundle
{
    protected function addFrontPathCompilerPass(
        ContainerBuilder $container,
        string $path
    ): void {
        $container->addCompilerPass(
            new DesignSystemTemplatesCompilerPass(
                $path,
                self::getAlias()
            )
        );

        // Add also to allow find all "front" folder, as in translations extension.
        $container->addCompilerPass(
            new DesignSystemTemplatesCompilerPass(
                $path,
                DesignSystemHelper::TWIG_NAMESPACE_FRONT
            )
        );
    }
}
