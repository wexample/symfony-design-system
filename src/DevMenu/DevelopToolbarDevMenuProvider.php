<?php

namespace Wexample\SymfonyDesignSystem\DevMenu;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Wexample\SymfonyDesignSystem\Interface\DevMenuProviderInterface;

/**
 * The develop toolbar of symfony-loader, opened over the page. Only where the
 * application turned it on (`develop_toolbar`): elsewhere, nothing.
 */
class DevelopToolbarDevMenuProvider implements DevMenuProviderInterface
{
    public function __construct(
        private readonly ParameterBagInterface $parameterBag,
    ) {
    }

    public function getDevMenuItems(): array
    {
        if (! $this->parameterBag->has('loader.develop_toolbar')
            || ! $this->parameterBag->get('loader.develop_toolbar')) {
            return [];
        }

        return [[
            'icon' => 'ph:bold/bug',
            'label' => 'WexampleSymfonyDesignSystemBundle.common.system::frontend.develop_toolbar',
            'href' => '#',
            'attr' => ['data-develop-toolbar-toggle' => '1'],
            // The page's own tool, before what opens elsewhere.
            'order' => -10,
        ]];
    }
}
