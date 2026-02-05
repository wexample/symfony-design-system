<?php

namespace Wexample\SymfonyDesignSystem\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Wexample\SymfonyHelpers\DependencyInjection\AbstractWexampleSymfonyExtension;
use Wexample\SymfonyLoader\DependencyInjection\Traits\WithLoaderConfigurationExtensionTrait;

class WexampleSymfonyDesignSystemExtension extends AbstractWexampleSymfonyExtension implements PrependExtensionInterface
{
    use WithLoaderConfigurationExtensionTrait;

    public function prepend(
        ContainerBuilder $container
    ): void {
        $layoutBases = (array) ($container->hasParameter('wexample_symfony_design_system.loader.layout_bases')
            ? $container->getParameter('wexample_symfony_design_system.loader.layout_bases')
            : []);

        $this->prependLoaderLayoutBases(
            $container,
            $this->normalizeLoaderLayoutBases($layoutBases)
        );
    }

    public function load(
        array $configs,
        ContainerBuilder $container
    ): void {
        $this->loadConfig(
            __DIR__,
            $container
        );
    }
}
