<?php

namespace Wexample\SymfonyDesignSystem;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Wexample\SymfonyDesignSystem\DependencyInjection\Compiler\DesignSystemTemplatesCompilerPass;
use Wexample\SymfonyDesignSystem\DependencyInjection\Compiler\TemplateBasedRoutesTagCompilerPass;
use Wexample\SymfonyDesignSystem\Interface\DesignSystemBundleInterface;
use Wexample\SymfonyHelpers\Class\AbstractBundle;
use Wexample\SymfonyHelpers\Helper\BundleHelper;

class WexampleSymfonyDesignSystemBundle extends AbstractBundle implements DesignSystemBundleInterface
{
    public static function getDesignSystemFrontPaths(): array
    {
        return [
            BundleHelper::getBundleCssAlias(static::class) => __DIR__.'/../assets/',
        ];
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(
            new DesignSystemTemplatesCompilerPass()
        );

        $container->addCompilerPass(
            new TemplateBasedRoutesTagCompilerPass()
        );
    }
}
