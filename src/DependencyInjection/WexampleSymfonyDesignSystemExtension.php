<?php

namespace Wexample\SymfonyDesignSystem\DependencyInjection;

use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Wexample\Helpers\Helper\ClassHelper;
use Wexample\SymfonyDesignSystem\Chat\SlashCommand\Attribute\AsSlashCommand;
use Wexample\SymfonyDesignSystem\Chat\SlashCommand\SlashCommandRegistry;
use Wexample\SymfonyDesignSystem\Class\ElementSource;
use Wexample\SymfonyDesignSystem\Enum\UiStateStorage;
use Wexample\SymfonyDesignSystem\Interface\AvatarEngineInterface;
use Wexample\SymfonyDesignSystem\Interface\DesignSystemElementsBundleInterface;
use Wexample\SymfonyDesignSystem\Interface\DevMenuProviderInterface;
use Wexample\SymfonyDesignSystem\Interface\RouteGroupVoterInterface;
use Wexample\SymfonyDesignSystem\Interface\UiStateStorageInterface;
use Wexample\SymfonyDesignSystem\Service\UiState\SessionUiStateStorage;
use Wexample\SymfonyDesignSystem\UiState\Service\DatabaseUiStateStorage;
use Wexample\SymfonyHelpers\DependencyInjection\AbstractWexampleSymfonyExtension;
use Wexample\SymfonyLoader\DependencyInjection\Traits\WithLoaderConfigurationExtensionTrait;

class WexampleSymfonyDesignSystemExtension extends AbstractWexampleSymfonyExtension
{
    use WithLoaderConfigurationExtensionTrait;

    final public const string PARAMETER_ELEMENT_SOURCES = 'wexample_symfony_design_system.element_sources';

    final public const string PARAMETER_UI_STATE_STORAGE = 'wexample_symfony_design_system.ui_state.storage';

    /**
     * The table of what the interface remembers is mapped only in an app that
     * keeps it in the database: every other app would otherwise grow a table it
     * never writes.
     */
    public function prepend(ContainerBuilder $container): void
    {
        parent::prepend($container);

        if (! self::uiStateStorage($container)->isDatabase()) {
            return;
        }

        $container->prependExtensionConfig('doctrine', [
            'orm' => [
                'mappings' => [
                    'WexampleSymfonyDesignSystemUiState' => [
                        'type' => 'attribute',
                        'dir' => realpath(__DIR__.'/../UiState/Entity'),
                        'prefix' => 'Wexample\\SymfonyDesignSystem\\UiState\\Entity',
                        'alias' => 'WexampleSymfonyDesignSystemUiState',
                        'is_bundle' => false,
                    ],
                ],
            ],
        ]);
    }

    public function load(
        array $configs,
        ContainerBuilder $container
    ): void {
        // Read before the bundle's own default is loaded over the app's choice.
        $uiStateStorage = self::uiStateStorage($container);

        $this->loadConfig(
            __DIR__,
            $container
        );

        if ($uiStateStorage->isDatabase()) {
            $this->loadConfig(__DIR__, $container, 'ui_state_database.yaml');
        }

        $container->setAlias(
            UiStateStorageInterface::class,
            $uiStateStorage->isDatabase() ? DatabaseUiStateStorage::class : SessionUiStateStorage::class
        );

        $container->registerAttributeForAutoconfiguration(
            AsSlashCommand::class,
            static function (ChildDefinition $definition): void {
                $definition->addTag(SlashCommandRegistry::TAG);
            }
        );

        // Tagged on the interface rather than through a compiler pass: a voter
        // written in the host application is autoconfigured like any other
        // service, so it joins the registry without the application knowing the
        // tag name.
        $container
            ->registerForAutoconfiguration(RouteGroupVoterInterface::class)
            ->addTag(RouteGroupVoterInterface::TAG);

        // And for the engines that draw avatars.
        $container
            ->registerForAutoconfiguration(AvatarEngineInterface::class)
            ->addTag(AvatarEngineInterface::TAG);

        // Same for what a bundle — or the app — puts in the development menu.
        $container
            ->registerForAutoconfiguration(DevMenuProviderInterface::class)
            ->addTag(DevMenuProviderInterface::TAG);

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

    private static function uiStateStorage(ContainerBuilder $container): UiStateStorage
    {
        return UiStateStorage::from(
            $container->hasParameter(self::PARAMETER_UI_STATE_STORAGE)
                ? $container->getParameter(self::PARAMETER_UI_STATE_STORAGE)
                : UiStateStorage::Session->value
        );
    }
}
