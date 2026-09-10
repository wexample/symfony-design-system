<?php

namespace Wexample\SymfonyDesignSystem\DependencyInjection;

use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Wexample\SymfonyDesignSystem\Chat\SlashCommand\Attribute\AsSlashCommand;
use Wexample\SymfonyDesignSystem\Chat\SlashCommand\SlashCommandRegistry;
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

        $container->registerAttributeForAutoconfiguration(
            AsSlashCommand::class,
            static function (ChildDefinition $definition): void {
                $definition->addTag(SlashCommandRegistry::TAG);
            }
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
