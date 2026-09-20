<?php

namespace Wexample\SymfonyDesignSystem\DependencyInjection;

use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Wexample\Helpers\Helper\ClassHelper;
use Wexample\SymfonyDesignSystem\Chat\SlashCommand\Attribute\AsSlashCommand;
use Wexample\SymfonyDesignSystem\Chat\SlashCommand\SlashCommandRegistry;
use Wexample\SymfonyDesignSystem\Class\ElementSource;
use Wexample\SymfonyDesignSystem\Interface\DesignSystemElementsBundleInterface;
use Wexample\SymfonyHelpers\DependencyInjection\AbstractWexampleSymfonyExtension;
use Wexample\SymfonyLoader\DependencyInjection\Traits\WithLoaderConfigurationExtensionTrait;

class WexampleSymfonyDesignSystemExtension extends AbstractWexampleSymfonyExtension
{
    use WithLoaderConfigurationExtensionTrait;

    final public const string PARAMETER_ELEMENT_SOURCES = 'wexample_symfony_design_system.element_sources';

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

        $container->setParameter(
            self::PARAMETER_ELEMENT_SOURCES,
            $this->collectElementSources($container)
        );
    }

    /**
     * The bundles that signed up as holding elements, in registration order.
     *
     * Same shape as the loader's front paths: the container walks the kernel's
     * bundles and keeps the ones implementing the interface. A bundle is a source
     * because it said so, never because it happens to publish assets.
     */
    private function collectElementSources(ContainerBuilder $container): array
    {
        $sources = [];

        foreach ((array) $container->getParameter('kernel.bundles') as $class) {
            if (ClassHelper::classImplementsInterface(
                $class,
                DesignSystemElementsBundleInterface::class
            )) {
                $sources[] = ElementSource::fromBundle($class)->toArray();
            }
        }

        return $sources;
    }
}
