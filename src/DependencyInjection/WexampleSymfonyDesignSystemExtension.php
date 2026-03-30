<?php

namespace Wexample\SymfonyDesignSystem\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Wexample\SymfonyHelpers\DependencyInjection\AbstractWexampleSymfonyExtension;
use Wexample\SymfonyLoader\DependencyInjection\Traits\WithLoaderConfigurationExtensionTrait;

class WexampleSymfonyDesignSystemExtension extends AbstractWexampleSymfonyExtension
{
    use WithLoaderConfigurationExtensionTrait;

    public function load(
        array $configs,
        ContainerBuilder $container
    ): void {
        $this->loadConfig(
            __DIR__,
            $container
        );

        $layoutBases = (array) ($container->hasParameter('wexample_symfony_design_system.loader.layout_bases')
            ? $container->getParameter('wexample_symfony_design_system.loader.layout_bases')
            : []);

        $this->mergeLoaderLayoutBasesParameter(
            $container,
            $layoutBases
        );
    }
}
