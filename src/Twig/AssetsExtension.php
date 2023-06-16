<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\TwigFunction;

class AssetsExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'assets_render_initial_aggregated',
                [
                    $this,
                    'assetsRenderInitialAggregated',
                ]
            ),
            new TwigFunction(
                'assets_type_filtered',
                [
                    $this,
                    'assetsTypeFiltered',
                ]
            ),
            new TwigFunction(
                'assets_preload_list',
                [
                    $this,
                    'assetsPreloadList',
                ]
            ),
        ];
    }

    public function assetsRenderInitialAggregated(string $pageName, string $type): string
    {
        return '';
    }

    public function assetsTypeFiltered(
        string $contextType,
        string $assetType = null
    ): array {
        return [];
    }

    public function assetsPreloadList(string $ext): array
    {
        return [];
    }
}
