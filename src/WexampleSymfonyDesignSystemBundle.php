<?php

namespace Wexample\SymfonyDesignSystem;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Wexample\SymfonyDesignSystem\DependencyInjection\Compiler\RouteAccessPass;
use Wexample\SymfonyDesignSystem\Interface\DesignSystemElementsBundleInterface;
use Wexample\SymfonyHelpers\Class\AbstractBundle;
use Wexample\SymfonyHelpers\Helper\BundleHelper;
use Wexample\SymfonyHelpers\Interface\LoaderBundleInterface;

class WexampleSymfonyDesignSystemBundle extends AbstractBundle implements LoaderBundleInterface, DesignSystemElementsBundleInterface
{
    public static function getLoaderFrontPaths(): array
    {
        return [
            BundleHelper::getBundleCssAlias(static::class) => __DIR__ . '/../assets/',
        ];
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new RouteAccessPass());
    }

    public static function getDesignSystemElementsPath(): string
    {
        return __DIR__ . '/../assets/';
    }
}
